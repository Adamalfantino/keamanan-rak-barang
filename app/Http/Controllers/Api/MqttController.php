<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\PirReading;
use App\Models\ReedSwitchReading;
use App\Models\VibrationReading;
use App\Models\DoorAccessReading;
use App\Models\LoRaMessage;
use App\Models\Alert;
use App\Services\MqttService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * MqttController
 *
 * Menangani dua skenario:
 *
 * 1. POST /api/mqtt/ingest
 *    Endpoint ini dipanggil oleh MQTT bridge/webhook ketika ada pesan
 *    masuk dari ESP32 ke broker HiveMQ. Bridge (misal: Node-RED, MQTT-HTTP
 *    bridge, atau script Python) subscribe ke topic dan forward ke sini.
 *
 * 2. POST /api/mqtt/publish
 *    Publish pesan dari backend ke MQTT broker (untuk kirim command ke ESP32).
 *
 * Format payload dari ESP32 (buildPayload di kode ESP32):
 * {
 *   "device_id": 1,
 *   "node": "NODE_001",
 *   "type": "PIR|REED|VIBRATION|HEARTBEAT",
 *   "motion": true/false,       (untuk PIR)
 *   "door": true/false,         (untuk REED)
 *   "x": 1.5,                   (untuk VIBRATION)
 *   ... field asli dari LoRa packet
 * }
 */
class MqttController extends Controller
{
    protected MqttService $mqttService;
    protected NotificationService $notificationService;

    public function __construct(MqttService $mqttService, NotificationService $notificationService)
    {
        $this->mqttService         = $mqttService;
        $this->notificationService = $notificationService;
    }

    // =========================================================================
    // INGEST — Terima data dari MQTT bridge/webhook
    // =========================================================================

    /**
     * POST /api/mqtt/ingest
     *
     * Dipanggil oleh MQTT bridge ketika ada pesan masuk dari ESP32.
     * Body request:
     * {
     *   "topic": "keamanan/pir",
     *   "payload": "{\"device_id\":1,\"node\":\"NODE_001\",\"type\":\"PIR\",\"motion\":true}"
     * }
     */
    public function ingest(Request $request): JsonResponse
    {
        try {
            // Verifikasi webhook secret (opsional)
            $secret = config('mqtt.webhook_secret');
            if ($secret) {
                $headerSecret = $request->header('X-MQTT-Secret');
                if ($headerSecret !== $secret) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
                }
            }

            $validator = Validator::make($request->all(), [
                'topic'   => 'required|string',
                'payload' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $topic      = $request->input('topic');
            $rawPayload = $request->input('payload');

            // Parse JSON payload
            $data = $this->mqttService->parseIncomingPayload($topic, $rawPayload);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payload format',
                ], 422);
            }

            Log::info('[MQTT] Ingest received', ['topic' => $topic, 'data' => $data]);

            // Route berdasarkan topic
            $sensorType = $this->mqttService->getSensorTypeFromTopic($topic);

            $result = match ($sensorType) {
                'PIR'       => $this->processPirData($data),
                'REED'      => $this->processReedData($data),
                'VIBRATION' => $this->processVibrationData($data),
                'HEARTBEAT' => $this->processHeartbeat($data),
                default     => $this->processUnknown($topic, $data),
            };

            return response()->json([
                'success' => true,
                'message' => 'MQTT data processed',
                'topic'   => $topic,
                'result'  => $result,
            ], 201);

        } catch (\Exception $e) {
            Log::error('[MQTT] Ingest error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process MQTT data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/mqtt/ingest-batch
     *
     * Untuk bridge yang mengirim banyak pesan sekaligus.
     * Body: { "messages": [ {"topic": "...", "payload": "..."}, ... ] }
     */
    public function ingestBatch(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'messages'          => 'required|array|min:1',
                'messages.*.topic'  => 'required|string',
                'messages.*.payload'=> 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $results = [];
            foreach ($request->input('messages') as $msg) {
                $data = $this->mqttService->parseIncomingPayload($msg['topic'], $msg['payload']);
                if (!$data) {
                    $results[] = ['topic' => $msg['topic'], 'success' => false, 'error' => 'Invalid JSON'];
                    continue;
                }

                $sensorType = $this->mqttService->getSensorTypeFromTopic($msg['topic']);
                $result = match ($sensorType) {
                    'PIR'       => $this->processPirData($data),
                    'REED'      => $this->processReedData($data),
                    'VIBRATION' => $this->processVibrationData($data),
                    'HEARTBEAT' => $this->processHeartbeat($data),
                    default     => $this->processUnknown($msg['topic'], $data),
                };

                $results[] = ['topic' => $msg['topic'], 'success' => true, 'result' => $result];
            }

            return response()->json([
                'success' => true,
                'processed' => count($results),
                'results'   => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('[MQTT] Batch ingest error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PUBLISH — Kirim pesan ke MQTT broker
    // =========================================================================

    /**
     * POST /api/mqtt/publish
     *
     * Publish pesan ke MQTT broker dari backend.
     * Body: { "topic": "keamanan/command", "payload": {...} }
     */
    public function publish(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'topic'   => 'required|string',
                'payload' => 'required|array',
                'qos'     => 'nullable|integer|in:0,1,2',
                'retain'  => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $success = $this->mqttService->publish(
                $request->input('topic'),
                $request->input('payload'),
                $request->input('qos', 0),
                $request->input('retain', false)
            );

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Message published' : 'Publish failed',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/mqtt/command
     *
     * Kirim command ke ESP32 node via MQTT.
     * Body: { "node_id": "NODE_001", "command": "REBOOT", "params": {} }
     */
    public function sendCommand(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'node_id' => 'required|string',
                'command' => 'required|string',
                'params'  => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $success = $this->mqttService->sendCommand(
                $request->input('node_id'),
                $request->input('command'),
                $request->input('params', [])
            );

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Command sent via MQTT' : 'Failed to send command',
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // PROCESSOR — Proses data per tipe sensor
    // =========================================================================

    /**
     * Proses data PIR dari MQTT
     *
     * Payload dari ESP32 (buildPayload):
     * { "device_id":1, "node":"NODE_001", "type":"PIR", "motion":true }
     *
     * Payload asli LoRa (routePacket):
     * { "node_id":"NODE_001", "gateway_id":"GATEWAY_001", "type":"PIR",
     *   "motion_detected":true, "device_id":1 }
     */
    private function processPirData(array $data): array
    {
        $device = $this->resolveDevice($data);

        if (!$device) {
            Log::warning('[MQTT] PIR: Device not found', $data);
            return ['success' => false, 'error' => 'Device not found'];
        }

        // Normalisasi field — ESP32 bisa kirim "motion" atau "motion_detected"
        $motionDetected = $data['motion'] ?? $data['motion_detected'] ?? false;
        $motionDetected = filter_var($motionDetected, FILTER_VALIDATE_BOOLEAN);

        $recordedAt      = now();
        $isAuthorizedTime = $this->checkAuthorizedTime($recordedAt);
        $motionIntensity  = $data['motion_intensity'] ?? ($motionDetected ? 50 : 0);
        $durationSeconds  = $data['duration_seconds'] ?? 0;

        $isSuspicious = $motionDetected && !$isAuthorizedTime;
        $motionType   = !$motionDetected ? 'none' : (!$isAuthorizedTime ? 'unauthorized' : ($isSuspicious ? 'suspicious' : 'normal'));

        $reading = PirReading::create([
            'device_id'         => $device->id,
            'motion_detected'   => $motionDetected,
            'motion_intensity'  => (int) $motionIntensity,
            'duration_seconds'  => (int) $durationSeconds,
            'is_authorized_time'=> $isAuthorizedTime,
            'is_suspicious'     => $isSuspicious,
            'motion_type'       => $motionType,
            'detection_zone'    => $data['detection_zone'] ?? 'center',
            'metadata'          => $this->buildMetadata($data),
            'recorded_at'       => $recordedAt,
        ]);

        Device::markOnline($device->id);

        if ($isSuspicious || $motionType === 'unauthorized') {
            $this->createAlert($device, 'motion_detected', 'high',
                'Gerakan Mencurigakan via MQTT',
                "Gerakan {$motionType} terdeteksi pada {$device->name} (via MQTT).",
                ['pir_reading_id' => $reading->id, 'motion_type' => $motionType]
            );
        }

        Log::info('[MQTT] PIR saved', ['id' => $reading->id, 'device' => $device->name]);

        return ['success' => true, 'reading_id' => $reading->id, 'motion_detected' => $motionDetected];
    }

    /**
     * Proses data Reed Switch dari MQTT
     *
     * Payload dari ESP32 (buildPayload):
     * { "device_id":1, "node":"NODE_001", "type":"REED", "door":true }
     *
     * Payload asli LoRa:
     * { "node_id":"NODE_001", "gateway_id":"GATEWAY_001", "type":"REED",
     *   "door_opened":true, "device_id":1 }
     */
    private function processReedData(array $data): array
    {
        $device = $this->resolveDevice($data);

        if (!$device) {
            Log::warning('[MQTT] REED: Device not found', $data);
            return ['success' => false, 'error' => 'Device not found'];
        }

        // Normalisasi field
        $doorOpen = $data['door'] ?? $data['door_opened'] ?? $data['door_open'] ?? false;
        $doorOpen = filter_var($doorOpen, FILTER_VALIDATE_BOOLEAN);

        $recordedAt      = now();
        $isAuthorizedTime = $this->checkAuthorizedTime($recordedAt);
        $accessMethod    = $data['access_method'] ?? 'unknown';
        $isForcedEntry   = filter_var($data['is_forced_entry'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $openDuration    = (int) ($data['open_duration_seconds'] ?? $data['duration_seconds'] ?? 0);

        // Deteksi paksa masuk
        if ($accessMethod === 'force' || $accessMethod === 'unknown') {
            $isForcedEntry = true;
        }

        $isAuthorized = !$isForcedEntry && ($accessMethod === 'emergency' || $isAuthorizedTime);
        $accessLevel  = $isForcedEntry ? 'emergency' : (!$isAuthorizedTime ? 'unauthorized' : 'normal');
        $doorStatus   = $isForcedEntry ? 'forced' : ($doorOpen ? 'open' : 'closed');

        $reading = ReedSwitchReading::create([
            'device_id'            => $device->id,
            'door_open'            => $doorOpen,
            'is_authorized'        => $isAuthorized,
            'is_forced_entry'      => $isForcedEntry,
            'access_method'        => $accessMethod,
            'door_status'          => $doorStatus,
            'open_duration_seconds'=> $openDuration,
            'access_level'         => $accessLevel,
            'door_location'        => $data['door_location'] ?? 'main',
            'metadata'             => $this->buildMetadata($data),
            'door_opened_at'       => $doorOpen ? $recordedAt : null,
            'recorded_at'          => $recordedAt,
        ]);

        Device::markOnline($device->id);

        if ($isForcedEntry || !$isAuthorized) {
            $this->createAlert($device, 'door_access_alert', $isForcedEntry ? 'critical' : 'high',
                'Akses Pintu Mencurigakan via MQTT',
                "Akses {$accessLevel} terdeteksi pada {$device->name} (via MQTT).",
                ['reed_reading_id' => $reading->id, 'access_level' => $accessLevel, 'door_open' => $doorOpen]
            );
        }

        Log::info('[MQTT] REED saved', ['id' => $reading->id, 'device' => $device->name]);

        return ['success' => true, 'reading_id' => $reading->id, 'door_open' => $doorOpen];
    }

    /**
     * Proses data Vibration dari MQTT
     *
     * Payload dari ESP32 (buildPayload):
     * { "device_id":1, "node":"NODE_001", "type":"VIBRATION", "x":1.5 }
     *
     * Payload asli LoRa:
     * { "node_id":"NODE_001", "gateway_id":"GATEWAY_001", "type":"VIBRATION",
     *   "x_axis":1.5, "y_axis":0.3, "z_axis":0.8, "device_id":1 }
     */
    private function processVibrationData(array $data): array
    {
        $device = $this->resolveDevice($data);

        if (!$device) {
            Log::warning('[MQTT] VIBRATION: Device not found', $data);
            return ['success' => false, 'error' => 'Device not found'];
        }

        // Normalisasi field — ESP32 buildPayload hanya kirim "x", tapi LoRa raw kirim "x_axis"
        $xAxis = (float) ($data['x'] ?? $data['x_axis'] ?? 0);
        $yAxis = (float) ($data['y'] ?? $data['y_axis'] ?? 0);
        $zAxis = (float) ($data['z'] ?? $data['z_axis'] ?? 0);

        $threshold = (float) ($data['threshold'] ?? 2.0);
        $magnitude = sqrt(pow($xAxis, 2) + pow($yAxis, 2) + pow($zAxis, 2));
        $isAbnormal = $magnitude > $threshold;
        $status = $magnitude <= $threshold ? 'normal' : ($magnitude <= $threshold * 1.5 ? 'warning' : 'critical');

        $reading = VibrationReading::create([
            'device_id'   => $device->id,
            'x_axis'      => $xAxis,
            'y_axis'      => $yAxis,
            'z_axis'      => $zAxis,
            'magnitude'   => $magnitude,
            'is_abnormal' => $isAbnormal,
            'threshold'   => $threshold,
            'status'      => $status,
            'metadata'    => $this->buildMetadata($data),
            'recorded_at' => now(),
        ]);

        Device::markOnline($device->id);

        if ($isAbnormal) {
            $this->createAlert($device, 'vibration_abnormal', $status === 'critical' ? 'high' : 'medium',
                'Getaran Abnormal via MQTT',
                "Getaran {$status} terdeteksi pada {$device->name}. Magnitude: " . round($magnitude, 2) . " (via MQTT).",
                ['vibration_reading_id' => $reading->id, 'magnitude' => $magnitude, 'status' => $status]
            );
        }

        Log::info('[MQTT] VIBRATION saved', ['id' => $reading->id, 'magnitude' => $magnitude]);

        return ['success' => true, 'reading_id' => $reading->id, 'magnitude' => $magnitude, 'status' => $status];
    }

    /**
     * Proses heartbeat dari MQTT
     */
    private function processHeartbeat(array $data): array
    {
        $device = $this->resolveDevice($data);

        if (!$device) {
            Log::warning('[MQTT] HEARTBEAT: Device not found', $data);
            return ['success' => false, 'error' => 'Device not found'];
        }

        Device::markOnline($device->id);

        Log::info('[MQTT] HEARTBEAT received', ['device' => $device->name, 'node' => $data['node'] ?? $data['node_id'] ?? 'unknown']);

        return ['success' => true, 'device' => $device->name, 'status' => 'online'];
    }

    /**
     * Proses topic yang tidak dikenal
     */
    private function processUnknown(string $topic, array $data): array
    {
        Log::warning('[MQTT] Unknown topic', ['topic' => $topic, 'data' => $data]);
        return ['success' => false, 'error' => "Unknown topic: {$topic}"];
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Resolve device dari payload.
     * ESP32 mengirim device_id = 1 (integer), coba cari by id dulu,
     * fallback ke device pertama yang aktif.
     */
    private function resolveDevice(array $data): ?Device
    {
        // Coba by numeric id
        if (!empty($data['device_id']) && is_numeric($data['device_id'])) {
            $device = Device::find((int) $data['device_id']);
            if ($device) return $device;
        }

        // Coba by node_id / node string
        $nodeId = $data['node'] ?? $data['node_id'] ?? null;
        if ($nodeId) {
            $device = Device::where('device_id', $nodeId)
                ->orWhere('name', 'like', '%' . $nodeId . '%')
                ->first();
            if ($device) return $device;
        }

        // Fallback: device pertama yang aktif
        return Device::where('is_active', true)->first() ?? Device::first();
    }

    /**
     * Cek apakah waktu dalam jam kerja (Senin-Jumat 07:00-18:00)
     */
    private function checkAuthorizedTime(Carbon $timestamp): bool
    {
        $hour = $timestamp->hour;
        return $timestamp->isWeekday() && $hour >= 7 && $hour < 18;
    }

    /**
     * Build metadata dari data MQTT (simpan field extra untuk audit)
     */
    private function buildMetadata(array $data): array
    {
        return [
            'source'      => 'mqtt',
            'node'        => $data['node'] ?? $data['node_id'] ?? null,
            'gateway'     => $data['gateway_id'] ?? null,
            'mqtt_topic'  => $data['_mqtt_topic'] ?? null,
            'received_at' => $data['_received_at'] ?? now()->toISOString(),
            'raw'         => array_diff_key($data, array_flip([
                'device_id', 'node', 'node_id', 'gateway_id', 'type',
                'motion', 'motion_detected', 'door', 'door_opened', 'door_open',
                'x', 'y', 'z', 'x_axis', 'y_axis', 'z_axis',
                '_mqtt_topic', '_received_at',
            ])),
        ];
    }

    /**
     * Buat alert record
     */
    private function createAlert(Device $device, string $type, string $priority, string $title, string $message, array $extraData = []): void
    {
        try {
            Alert::create([
                'device_id' => $device->id,
                'type'      => $type,
                'priority'  => $priority,
                'title'     => $title,
                'message'   => $message,
                'data'      => $extraData,
                'is_read'   => false,
            ]);
        } catch (\Exception $e) {
            Log::error('[MQTT] Failed to create alert: ' . $e->getMessage());
        }
    }
}
