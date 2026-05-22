<?php

namespace App\Services;

use App\Models\LoRaMessage;
use App\Models\VibrationReading;
use App\Models\PirReading;
use App\Models\DoorAccessReading;
use App\Models\Device;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LoRaProcessingService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Process inbound LoRa message
     */
    public function processInboundMessage(LoRaMessage $message): array
    {
        try {
            // Parse payload
            $parsedData = $message->parsePayload();
            
            // Update message dengan parsed data
            $message->update([
                'parsed_data' => $parsedData
            ]);

            $result = ['success' => false, 'action' => 'none'];

            // Process berdasarkan message type
            switch ($message->message_type) {
                case 'sensor_data':
                    $result = $this->processSensorData($message, $parsedData);
                    break;
                    
                case 'heartbeat':
                    $result = $this->processHeartbeat($message, $parsedData);
                    break;
                    
                case 'ack':
                    $result = $this->processAcknowledgment($message, $parsedData);
                    break;
                    
                default:
                    $result = ['success' => true, 'action' => 'logged'];
            }

            // Mark message as processed
            $message->markAsProcessed($result['success'], $result['error'] ?? null);

            Log::info("LoRa message processed", [
                'message_id' => $message->id,
                'node_id' => $message->node_id,
                'message_type' => $message->message_type,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            $message->markAsProcessed(false, $e->getMessage());
            
            Log::error("LoRa message processing failed", [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process sensor data dari LoRa message
     */
    private function processSensorData(LoRaMessage $message, array $parsedData): array
    {
        if (!isset($parsedData['sensor_type'])) {
            return ['success' => false, 'error' => 'Missing sensor type'];
        }

        $sensorType = strtoupper($parsedData['sensor_type']);
        
        switch ($sensorType) {
            case 'VIBRATION':
                return $this->processVibrationData($message, $parsedData);
                
            case 'PIR':
                return $this->processPirData($message, $parsedData);
                
            case 'DOOR':
                return $this->processDoorAccessData($message, $parsedData);
                
            default:
                return ['success' => false, 'error' => 'Unknown sensor type: ' . $sensorType];
        }
    }

    /**
     * Process vibration sensor data
     */
    private function processVibrationData(LoRaMessage $message, array $data): array
    {
        try {
            // Hitung magnitude
            $magnitude = sqrt(
                pow($data['x_axis'] ?? 0, 2) + 
                pow($data['y_axis'] ?? 0, 2) + 
                pow($data['z_axis'] ?? 0, 2)
            );

            $threshold = $data['threshold'] ?? 2.0;
            $isAbnormal = $magnitude > $threshold;
            
            $status = 'normal';
            if ($magnitude > $threshold * 1.5) {
                $status = 'critical';
            } elseif ($magnitude > $threshold) {
                $status = 'warning';
            }

            // Simpan ke VibrationReading
            $vibrationReading = VibrationReading::create([
                'device_id' => $message->device_id,
                'x_axis' => $data['x_axis'] ?? 0,
                'y_axis' => $data['y_axis'] ?? 0,
                'z_axis' => $data['z_axis'] ?? 0,
                'magnitude' => $magnitude,
                'is_abnormal' => $isAbnormal,
                'threshold' => $threshold,
                'status' => $status,
                'metadata' => [
                    'lora_message_id' => $message->id,
                    'node_id' => $message->node_id,
                    'rssi' => $message->rssi,
                    'snr' => $message->snr
                ],
                'recorded_at' => $message->received_at ?? now()
            ]);

            return [
                'success' => true,
                'action' => 'vibration_data_saved',
                'vibration_reading_id' => $vibrationReading->id,
                'magnitude' => $magnitude,
                'status' => $status,
                'is_abnormal' => $isAbnormal
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Vibration processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process PIR sensor data
     */
    private function processPirData(LoRaMessage $message, array $data): array
    {
        try {
            $isAuthorizedTime = $this->checkAuthorizedTime();
            $isSuspicious = $this->determineSuspiciousPirMotion($data, $isAuthorizedTime);
            
            $motionType = 'normal';
            if (!$isAuthorizedTime) {
                $motionType = 'unauthorized';
            } elseif ($isSuspicious) {
                $motionType = 'suspicious';
            }

            // Simpan ke PirReading
            $pirReading = PirReading::create([
                'device_id' => $message->device_id,
                'motion_detected' => $data['motion_detected'] ?? false,
                'motion_intensity' => $data['motion_intensity'] ?? 0,
                'duration_seconds' => $data['duration_seconds'] ?? 0,
                'is_authorized_time' => $isAuthorizedTime,
                'is_suspicious' => $isSuspicious,
                'motion_type' => $motionType,
                'detection_zone' => $data['detection_zone'] ?? 'center',
                'metadata' => [
                    'lora_message_id' => $message->id,
                    'node_id' => $message->node_id,
                    'rssi' => $message->rssi,
                    'snr' => $message->snr
                ],
                'recorded_at' => $message->received_at ?? now()
            ]);

            return [
                'success' => true,
                'action' => 'pir_data_saved',
                'pir_reading_id' => $pirReading->id,
                'motion_type' => $motionType,
                'is_suspicious' => $isSuspicious
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'PIR processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process door access sensor data
     */
    private function processDoorAccessData(LoRaMessage $message, array $data): array
    {
        try {
            $isAuthorizedTime = $this->checkAuthorizedTime();
            $isAuthorizedAccess = $this->determineAuthorizedDoorAccess($data, $isAuthorizedTime);
            $isSuspicious = $this->determineSuspiciousDoorAccess($data, $isAuthorizedAccess);
            
            $accessType = $this->determineDoorAccessType($data, $isAuthorizedAccess, $isAuthorizedTime);

            // Simpan ke DoorAccessReading
            $doorReading = DoorAccessReading::create([
                'device_id' => $message->device_id,
                'door_opened' => $data['door_opened'] ?? false,
                'is_authorized_access' => $isAuthorizedAccess,
                'access_type' => $accessType,
                'access_method' => $data['access_method'] ?? 'unknown',
                'user_id_card' => $data['user_id_card'] ?? null,
                'duration_seconds' => $data['duration_seconds'] ?? 0,
                'is_suspicious' => $isSuspicious,
                'door_location' => $data['door_location'] ?? 'main_entrance',
                'is_forced_entry' => ($data['access_method'] ?? '') === 'force',
                'metadata' => [
                    'lora_message_id' => $message->id,
                    'node_id' => $message->node_id,
                    'rssi' => $message->rssi,
                    'snr' => $message->snr
                ],
                'recorded_at' => $message->received_at ?? now()
            ]);

            return [
                'success' => true,
                'action' => 'door_access_data_saved',
                'door_reading_id' => $doorReading->id,
                'access_type' => $accessType,
                'is_suspicious' => $isSuspicious
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Door access processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process heartbeat message
     */
    private function processHeartbeat(LoRaMessage $message, array $data): array
    {
        try {
            // Update device last seen
            if ($message->device) {
                $message->device->update([
                    'last_seen' => $message->received_at ?? now(),
                    'status' => 'online'
                ]);
            }

            // Check battery level untuk alert
            $batteryLevel = $data['battery_level'] ?? null;
            if ($batteryLevel !== null && $batteryLevel < 20) {
                $this->sendLowBatteryAlert($message, $batteryLevel);
            }

            return [
                'success' => true,
                'action' => 'heartbeat_processed',
                'battery_level' => $batteryLevel,
                'signal_strength' => $data['signal_strength'] ?? null,
                'uptime' => $data['uptime_seconds'] ?? null
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Heartbeat processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process acknowledgment message
     */
    private function processAcknowledgment(LoRaMessage $message, array $data): array
    {
        try {
            $commandId = $data['command_id'] ?? null;
            
            if ($commandId) {
                // Cari outbound message yang sesuai
                $outboundMessage = LoRaMessage::where('direction', 'outbound')
                    ->where('metadata->command_id', $commandId)
                    ->first();
                
                if ($outboundMessage) {
                    $outboundMessage->markAsAcknowledged();
                }
            }

            return [
                'success' => true,
                'action' => 'acknowledgment_processed',
                'command_id' => $commandId,
                'ack_status' => $data['ack_status'] ?? 'unknown'
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'ACK processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Transmit message ke LoRa gateway
     */
    public function transmitMessage(LoRaMessage $message): array
    {
        try {
            // Implementasi tergantung jenis LoRa gateway yang digunakan
            // Contoh untuk HTTP-based gateway
            
            $gatewayUrl = config('lora.gateway_url', 'http://localhost:8080/lora/send');
            
            $payload = [
                'node_id' => $message->node_id,
                'payload' => $message->payload,
                'spreading_factor' => $message->spreading_factor ?? 7,
                'frequency' => $message->frequency ?? 868.1,
                'tx_power' => 14
            ];

            // Kirim ke gateway (simulasi)
            Log::info("LoRa message would be transmitted", [
                'gateway_url' => $gatewayUrl,
                'payload' => $payload
            ]);

            // TODO: Implementasi actual transmission
            /*
            $response = Http::timeout(10)->post($gatewayUrl, $payload);
            
            if ($response->successful()) {
                $message->update(['status' => 'transmitted']);
                return ['success' => true, 'gateway_response' => $response->json()];
            } else {
                $message->update(['status' => 'failed']);
                return ['success' => false, 'error' => 'Gateway transmission failed'];
            }
            */

            // Simulasi sukses
            $message->update(['status' => 'transmitted']);
            return ['success' => true, 'simulated' => true];

        } catch (\Exception $e) {
            $message->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            
            Log::error("LoRa transmission failed", [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send low battery alert
     */
    private function sendLowBatteryAlert(LoRaMessage $message, int $batteryLevel): void
    {
        try {
            Log::warning("Low battery alert", [
                'node_id' => $message->node_id,
                'battery_level' => $batteryLevel,
                'rssi' => $message->rssi
            ]);

            // TODO: Implementasi notification untuk low battery
            // $this->notificationService->sendLowBatteryAlert($message, $batteryLevel);

        } catch (\Exception $e) {
            Log::error("Failed to send low battery alert: " . $e->getMessage());
        }
    }

    /**
     * Check authorized time (jam kerja)
     */
    private function checkAuthorizedTime(): bool
    {
        $hour = now()->hour;
        return $hour >= 8 && $hour < 17;
    }

    /**
     * Determine suspicious PIR motion
     */
    private function determineSuspiciousPirMotion(array $data, bool $isAuthorizedTime): bool
    {
        if (!$isAuthorizedTime) {
            return true;
        }
        
        $intensity = $data['motion_intensity'] ?? 0;
        $duration = $data['duration_seconds'] ?? 0;
        
        return $intensity > 80 || $duration > 300;
    }

    /**
     * Determine authorized door access
     */
    private function determineAuthorizedDoorAccess(array $data, bool $isAuthorizedTime): bool
    {
        $method = $data['access_method'] ?? 'unknown';
        $idCard = $data['user_id_card'] ?? null;
        
        if ($method === 'force') {
            return false;
        }
        
        if ($method === 'emergency') {
            return true;
        }
        
        if ($method === 'keycard' && $idCard && preg_match('/^EMP-\d{4}$/', $idCard)) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine suspicious door access
     */
    private function determineSuspiciousDoorAccess(array $data, bool $isAuthorizedAccess): bool
    {
        if (!$isAuthorizedAccess) {
            return true;
        }
        
        $duration = $data['duration_seconds'] ?? 0;
        return $duration > 180;
    }

    /**
     * Determine door access type
     */
    private function determineDoorAccessType(array $data, bool $isAuthorizedAccess, bool $isAuthorizedTime): string
    {
        $method = $data['access_method'] ?? 'unknown';
        
        if ($method === 'force') {
            return 'forced_entry';
        }
        
        if ($method === 'emergency') {
            return 'emergency';
        }
        
        if ($method === 'maintenance') {
            return 'maintenance';
        }
        
        if ($isAuthorizedAccess && $isAuthorizedTime) {
            return 'authorized';
        }
        
        if (!$isAuthorizedTime) {
            return 'after_hours';
        }
        
        return 'unauthorized';
    }

    /**
     * Send command to LoRa node
     */
    public function sendCommandToNode(string $nodeId, string $action, array $parameters = []): array
    {
        try {
            $commandId = uniqid('CMD_');
            $payload = LoRaMessage::generateCommandPayload($action, $parameters, $commandId);
            
            $message = LoRaMessage::create([
                'node_id' => $nodeId,
                'gateway_id' => 'GATEWAY_001',
                'direction' => 'outbound',
                'message_type' => 'command',
                'payload' => $payload,
                'status' => 'pending',
                'metadata' => [
                    'command_id' => $commandId,
                    'action' => $action,
                    'parameters' => $parameters
                ],
                'transmitted_at' => now()
            ]);

            $result = $this->transmitMessage($message);
            
            return array_merge($result, [
                'message_id' => $message->id,
                'command_id' => $commandId
            ]);

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send configuration to LoRa node
     */
    public function sendConfigToNode(string $nodeId, string $parameter, $value): array
    {
        try {
            $configId = uniqid('CFG_');
            $payload = LoRaMessage::generateConfigPayload($parameter, $value, $configId);
            
            $message = LoRaMessage::create([
                'node_id' => $nodeId,
                'gateway_id' => 'GATEWAY_001',
                'direction' => 'outbound',
                'message_type' => 'config',
                'payload' => $payload,
                'status' => 'pending',
                'metadata' => [
                    'config_id' => $configId,
                    'parameter' => $parameter,
                    'value' => $value
                ],
                'transmitted_at' => now()
            ]);

            $result = $this->transmitMessage($message);
            
            return array_merge($result, [
                'message_id' => $message->id,
                'config_id' => $configId
            ]);

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}