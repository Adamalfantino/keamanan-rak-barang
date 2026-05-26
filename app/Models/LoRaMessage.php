<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoRaMessage extends Model
{
    protected $fillable = [
        'device_id',
        'node_id',
        'gateway_id',
        'direction',
        'message_type',
        'payload',
        'parsed_data',
        'rssi',
        'snr',
        'spreading_factor',
        'frequency',
        'bandwidth',
        'is_processed',
        'is_acknowledged',
        'status',
        'error_message',
        'metadata',
        'transmitted_at',
        'received_at'
    ];

    protected $casts = [
        'parsed_data' => 'array',
        'metadata' => 'array',
        'is_processed' => 'boolean',
        'is_acknowledged' => 'boolean',
        'transmitted_at' => 'datetime',
        'received_at' => 'datetime',
        'rssi' => 'float',
        'snr' => 'float',
        'frequency' => 'float'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Parse LoRa payload berdasarkan message type
     *
     * Mendukung dua format:
     * 1. JSON dari ESP32: {"node_id":"NODE_001","type":"PIR","motion_detected":true,...}
     * 2. Pipe-delimited: "SENSOR|PIR|1|85|120|front"
     */
    public function parsePayload(): array
    {
        try {
            // Coba parse sebagai JSON dulu (format ESP32 baru)
            $json = json_decode($this->payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                return $this->parseJsonPayload($json);
            }

            // Fallback ke format pipe-delimited (format lama)
            switch ($this->message_type) {
                case 'sensor_data':
                    return $this->parseSensorData();
                case 'heartbeat':
                    return $this->parseHeartbeat();
                case 'command':
                    return $this->parseCommand();
                case 'ack':
                    return $this->parseAcknowledgment();
                case 'config':
                    return $this->parseConfig();
                default:
                    return $this->parseGeneric();
            }
        } catch (\Exception $e) {
            Log::error("LoRa payload parsing failed: " . $e->getMessage(), [
                'message_id' => $this->id,
                'payload'    => $this->payload,
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Parse JSON payload dari ESP32
     *
     * Format ESP32 routePacket (raw LoRa JSON):
     * { "node_id":"NODE_001", "gateway_id":"GATEWAY_001", "type":"PIR",
     *   "motion_detected":true, "device_id":1 }
     *
     * Format ESP32 buildPayload (MQTT):
     * { "device_id":1, "node":"NODE_001", "type":"PIR", "motion":true }
     */
    private function parseJsonPayload(array $json): array
    {
        $type = strtoupper($json['type'] ?? '');

        switch ($type) {
            case 'PIR':
                return [
                    'sensor_type'      => 'PIR',
                    'motion_detected'  => filter_var($json['motion_detected'] ?? $json['motion'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'motion_intensity' => (int) ($json['motion_intensity'] ?? 50),
                    'duration_seconds' => (int) ($json['duration_seconds'] ?? 0),
                    'detection_zone'   => $json['detection_zone'] ?? 'center',
                ];

            case 'REED':
            case 'DOOR':
                return [
                    'sensor_type'      => 'DOOR',
                    'door_opened'      => filter_var($json['door_opened'] ?? $json['door'] ?? $json['door_open'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'access_method'    => $json['access_method'] ?? 'unknown',
                    'user_id_card'     => $json['user_id_card'] ?? null,
                    'duration_seconds' => (int) ($json['duration_seconds'] ?? 0),
                    'door_location'    => $json['door_location'] ?? 'main_entrance',
                ];

            case 'VIBRATION':
                $x = (float) ($json['x_axis'] ?? $json['x'] ?? 0);
                $y = (float) ($json['y_axis'] ?? $json['y'] ?? 0);
                $z = (float) ($json['z_axis'] ?? $json['z'] ?? 0);
                return [
                    'sensor_type' => 'VIBRATION',
                    'x_axis'      => $x,
                    'y_axis'      => $y,
                    'z_axis'      => $z,
                    'threshold'   => (float) ($json['threshold'] ?? 2.0),
                ];

            case 'HEARTBEAT':
                return [
                    'message_type'    => 'heartbeat',
                    'battery_level'   => $json['battery_level'] ?? null,
                    'signal_strength' => $json['signal_strength'] ?? null,
                    'uptime_seconds'  => $json['uptime'] ?? null,
                    'timestamp'       => now()->toISOString(),
                ];

            default:
                return array_merge(['sensor_type' => $type], $json);
        }
    }

    /**
     * Parse sensor data payload
     * Format: "SENSOR|VIBRATION|1.5,2.3,1.8|2.0" atau "SENSOR|PIR|1|85|120|front" atau "SENSOR|DOOR|1|EMP-1234|45|keycard"
     */
    private function parseSensorData(): array
    {
        $parts = explode('|', $this->payload);
        
        if (count($parts) < 3) {
            throw new \Exception('Invalid sensor data format');
        }

        $sensorType = strtoupper($parts[1]);
        $data = ['sensor_type' => $sensorType];

        switch ($sensorType) {
            case 'VIBRATION':
                // Format: SENSOR|VIBRATION|x,y,z|threshold
                if (count($parts) >= 4) {
                    $axes = explode(',', $parts[2]);
                    $data = array_merge($data, [
                        'x_axis' => (float)($axes[0] ?? 0),
                        'y_axis' => (float)($axes[1] ?? 0),
                        'z_axis' => (float)($axes[2] ?? 0),
                        'threshold' => (float)($parts[3] ?? 2.0)
                    ]);
                }
                break;

            case 'PIR':
                // Format: SENSOR|PIR|detected|intensity|duration|zone
                if (count($parts) >= 6) {
                    $data = array_merge($data, [
                        'motion_detected' => (bool)$parts[2],
                        'motion_intensity' => (int)$parts[3],
                        'duration_seconds' => (int)$parts[4],
                        'detection_zone' => $parts[5]
                    ]);
                }
                break;

            case 'DOOR':
                // Format: SENSOR|DOOR|opened|id_card|duration|method|location
                if (count($parts) >= 7) {
                    $data = array_merge($data, [
                        'door_opened' => (bool)$parts[2],
                        'user_id_card' => $parts[3] !== 'NULL' ? $parts[3] : null,
                        'duration_seconds' => (int)$parts[4],
                        'access_method' => $parts[5],
                        'door_location' => $parts[6]
                    ]);
                }
                break;

            default:
                $data['raw_data'] = array_slice($parts, 2);
        }

        return $data;
    }

    /**
     * Parse heartbeat payload
     * Format: "HEARTBEAT|battery_level|signal_strength|uptime"
     */
    private function parseHeartbeat(): array
    {
        $parts = explode('|', $this->payload);
        
        return [
            'message_type' => 'heartbeat',
            'battery_level' => isset($parts[1]) ? (int)$parts[1] : null,
            'signal_strength' => isset($parts[2]) ? (int)$parts[2] : null,
            'uptime_seconds' => isset($parts[3]) ? (int)$parts[3] : null,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Parse command payload
     * Format: "COMMAND|action|parameters"
     */
    private function parseCommand(): array
    {
        $parts = explode('|', $this->payload);
        
        return [
            'message_type' => 'command',
            'action' => $parts[1] ?? 'unknown',
            'parameters' => isset($parts[2]) ? explode(',', $parts[2]) : [],
            'command_id' => $parts[3] ?? null
        ];
    }

    /**
     * Parse acknowledgment payload
     * Format: "ACK|command_id|status|message"
     */
    private function parseAcknowledgment(): array
    {
        $parts = explode('|', $this->payload);
        
        return [
            'message_type' => 'ack',
            'command_id' => $parts[1] ?? null,
            'ack_status' => $parts[2] ?? 'unknown',
            'ack_message' => $parts[3] ?? null
        ];
    }

    /**
     * Parse config payload
     * Format: "CONFIG|parameter|value"
     */
    private function parseConfig(): array
    {
        $parts = explode('|', $this->payload);
        
        return [
            'message_type' => 'config',
            'parameter' => $parts[1] ?? null,
            'value' => $parts[2] ?? null,
            'config_id' => $parts[3] ?? null
        ];
    }

    /**
     * Parse generic payload
     */
    private function parseGeneric(): array
    {
        return [
            'message_type' => 'generic',
            'raw_payload' => $this->payload,
            'parts' => explode('|', $this->payload)
        ];
    }

    /**
     * Generate command payload untuk dikirim ke LoRa node
     */
    public static function generateCommandPayload(string $action, array $parameters = [], string $commandId = null): string
    {
        $commandId = $commandId ?? uniqid('CMD_');
        $paramStr = implode(',', $parameters);
        
        return "COMMAND|{$action}|{$paramStr}|{$commandId}";
    }

    /**
     * Generate config payload untuk konfigurasi node
     */
    public static function generateConfigPayload(string $parameter, $value, string $configId = null): string
    {
        $configId = $configId ?? uniqid('CFG_');
        
        return "CONFIG|{$parameter}|{$value}|{$configId}";
    }

    /**
     * Cek kualitas sinyal LoRa
     */
    public function getSignalQuality(): string
    {
        if ($this->rssi === null) {
            return 'unknown';
        }

        // RSSI thresholds (dBm)
        if ($this->rssi >= -70) {
            return 'excellent';
        } elseif ($this->rssi >= -85) {
            return 'good';
        } elseif ($this->rssi >= -100) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Estimate jarak berdasarkan RSSI (rough calculation)
     */
    public function estimateDistance(): ?float
    {
        if ($this->rssi === null) {
            return null;
        }

        // Simplified path loss calculation
        // Distance (km) = 10^((Tx_Power - RSSI - 32.44 - 20*log10(frequency)) / 20)
        $txPower = 14; // Typical LoRa TX power in dBm
        $frequency = $this->frequency ?? 868.0; // MHz
        
        $pathLoss = $txPower - $this->rssi - 32.44 - (20 * log10($frequency));
        $distance = pow(10, $pathLoss / 20);
        
        return round($distance, 2);
    }

    /**
     * Scope untuk inbound messages
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    /**
     * Scope untuk outbound messages
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    /**
     * Scope untuk unprocessed messages
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    /**
     * Scope untuk message type tertentu
     */
    public function scopeMessageType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Scope untuk node tertentu
     */
    public function scopeFromNode($query, $nodeId)
    {
        return $query->where('node_id', $nodeId);
    }

    /**
     * Scope untuk gateway tertentu
     */
    public function scopeFromGateway($query, $gatewayId)
    {
        return $query->where('gateway_id', $gatewayId);
    }

    /**
     * Mark message as processed
     */
    public function markAsProcessed(bool $success = true, string $errorMessage = null): void
    {
        $this->update([
            'is_processed' => true,
            'status' => $success ? 'processed' : 'failed',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Mark outbound message as acknowledged
     */
    public function markAsAcknowledged(): void
    {
        $this->update([
            'is_acknowledged' => true,
            'status' => 'acknowledged'
        ]);
    }

    /**
     * Get message summary untuk dashboard
     */
    public function getMessageSummary(): array
    {
        return [
            'id' => $this->id,
            'node_id' => $this->node_id,
            'direction' => $this->direction,
            'message_type' => $this->message_type,
            'signal_quality' => $this->getSignalQuality(),
            'estimated_distance' => $this->estimateDistance(),
            'is_processed' => $this->is_processed,
            'status' => $this->status,
            'received_at' => $this->received_at?->format('Y-m-d H:i:s'),
            'transmitted_at' => $this->transmitted_at?->format('Y-m-d H:i:s')
        ];
    }
}
