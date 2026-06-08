<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VibrationReading;
use App\Models\Device;
use App\Models\Alert;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VibrationController extends Controller
{
    /**
     * Terima data sensor getar dari IoT device
     *
     * Mendukung dua format payload:
     * 1. Format ESP32 LoRa raw: { node_id, gateway_id, type, x_axis, y_axis, z_axis, device_id }
     * 2. Format MQTT buildPayload: { device_id, node, type, x, y, z }
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Normalisasi payload dari ESP32
            $input = $this->normalizeEsp32Payload($request->all());

            // Validasi input
            $validator = Validator::make($input, [
                'device_id' => 'nullable|integer',
                'x_axis' => 'required|numeric',
                'y_axis' => 'required|numeric', 
                'z_axis' => 'required|numeric',
                'threshold' => 'nullable|numeric|min:0',
                'metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Resolve device
            $device = $this->resolveDevice($data, $input);
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found. Pastikan device sudah terdaftar di database.',
                ], 422);
            }
            $data['device_id'] = $device->id;
            
            // Hitung magnitude getaran
            $magnitude = sqrt(
                pow($data['x_axis'], 2) + 
                pow($data['y_axis'], 2) + 
                pow($data['z_axis'], 2)
            );

            // Set threshold default jika tidak ada
            $threshold = $data['threshold'] ?? 2.0;
            
            // Tentukan status getaran
            $isAbnormal = $magnitude > $threshold;
            $status = $this->determineStatus($magnitude, $threshold);

            // Simpan data vibration
            $vibrationReading = VibrationReading::create([
                'device_id' => $data['device_id'],
                'x_axis' => $data['x_axis'],
                'y_axis' => $data['y_axis'],
                'z_axis' => $data['z_axis'],
                'magnitude' => $magnitude,
                'is_abnormal' => $isAbnormal,
                'threshold' => $threshold,
                'status' => $status,
                'metadata' => $data['metadata'] ?? null,
                'recorded_at' => now()
            ]);

            // Tandai device sebagai online
            Device::markOnline($data['device_id']);

            // Tulis activity log
            $sensor = \App\Models\Sensor::where('device_id', $data['device_id'])->where('type', 'vibration')->first();
            \App\Models\ActivityLog::create([
                'device_id'  => $data['device_id'],
                'sensor_id'  => $sensor?->id,
                'event_type' => $isAbnormal ? 'vibration_detected' : 'system_normal',
                'severity'   => $status === 'critical' ? 'critical' : ($isAbnormal ? 'warning' : 'info'),
                'title'      => $isAbnormal ? 'Getaran Abnormal Terdeteksi' : 'Getaran Normal',
                'description'=> "Sensor SW-420 mendeteksi getaran. Magnitude: " . round($magnitude, 2) . ", Status: {$status}.",
                'event_data' => ['vibration_reading_id' => $vibrationReading->id, 'magnitude' => $magnitude, 'status' => $status],
                'location'   => $sensor?->device?->location ?? 'Rak A - Lantai 1',
                'event_time' => now(),
            ]);

            // Jika getaran abnormal, buat alert dan kirim notifikasi
            if ($isAbnormal) {
                $this->handleAbnormalVibration($vibrationReading);
            }

            return response()->json([
                'success' => true,
                'message' => 'Vibration data received successfully',
                'data' => [
                    'id' => $vibrationReading->id,
                    'magnitude' => $magnitude,
                    'status' => $status,
                    'is_abnormal' => $isAbnormal,
                    'alert_sent' => $isAbnormal
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Vibration data receive error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process vibration data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data vibration terbaru
     */
    public function getLatestReadings(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $limit = $request->get('limit', 50);
            $status = $request->get('status');

            $query = VibrationReading::with('device')
                ->orderBy('recorded_at', 'desc');

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $readings = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $readings,
                'count' => $readings->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vibration data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil statistik getaran
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $hours = $request->get('hours', 24);

            $query = VibrationReading::where('recorded_at', '>=', now()->subHours($hours));

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $stats = [
                'total_readings' => $query->count(),
                'normal_count' => $query->where('status', 'normal')->count(),
                'warning_count' => $query->where('status', 'warning')->count(),
                'critical_count' => $query->where('status', 'critical')->count(),
                'abnormal_percentage' => 0,
                'avg_magnitude' => $query->avg('magnitude'),
                'max_magnitude' => $query->max('magnitude'),
                'latest_reading' => $query->orderBy('recorded_at', 'desc')->first()
            ];

            if ($stats['total_readings'] > 0) {
                $abnormalCount = $stats['warning_count'] + $stats['critical_count'];
                $stats['abnormal_percentage'] = round(($abnormalCount / $stats['total_readings']) * 100, 2);
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
                'period_hours' => $hours
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Normalisasi payload dari ESP32 ke format yang diharapkan controller.
     *
     * ESP32 LoRa raw: { node_id, gateway_id, type, x_axis, y_axis, z_axis, device_id }
     * ESP32 MQTT buildPayload: { device_id, node, type, x, y, z }
     */
    private function normalizeEsp32Payload(array $input): array
    {
        // Normalisasi axis: "x" -> "x_axis", "y" -> "y_axis", "z" -> "z_axis"
        if (!isset($input['x_axis']) && isset($input['x'])) {
            $input['x_axis'] = $input['x'];
        }
        if (!isset($input['y_axis']) && isset($input['y'])) {
            $input['y_axis'] = $input['y'];
        }
        if (!isset($input['z_axis']) && isset($input['z'])) {
            $input['z_axis'] = $input['z'];
        }

        // Jika hanya ada x_axis tapi tidak y dan z (ESP32 hanya kirim x dari buildPayload)
        if (isset($input['x_axis']) && !isset($input['y_axis'])) {
            $input['y_axis'] = 0;
        }
        if (isset($input['x_axis']) && !isset($input['z_axis'])) {
            $input['z_axis'] = 0;
        }

        return $input;
    }

    /**
     * Resolve device dari payload — cari by id, fallback ke device pertama aktif
     */
    private function resolveDevice(array $data, array $rawInput): ?\App\Models\Device
    {
        if (!empty($data['device_id'])) {
            $device = \App\Models\Device::find((int) $data['device_id']);
            if ($device) return $device;
        }

        $nodeId = $rawInput['node_id'] ?? $rawInput['node'] ?? null;
        if ($nodeId) {
            $device = \App\Models\Device::where('device_id', $nodeId)->first();
            if ($device) return $device;
        }

        return \App\Models\Device::where('is_active', true)->first() ?? \App\Models\Device::first();
    }

    /**
     * Tentukan status berdasarkan magnitude dan threshold
     */
    private function determineStatus(float $magnitude, float $threshold): string
    {
        if ($magnitude <= $threshold) {
            return 'normal';
        } elseif ($magnitude <= $threshold * 1.5) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Handle getaran abnormal - buat alert dan kirim notifikasi
     */
    private function handleAbnormalVibration(VibrationReading $reading): void
    {
        try {
            $device = $reading->device;
            
            // Buat alert
            $alert = Alert::create([
                'device_id' => $reading->device_id,
                'type' => 'vibration_abnormal',
                'priority' => $reading->status === 'critical' ? 'high' : 'medium',
                'title' => 'Getaran Tidak Normal Terdeteksi',
                'message' => "Getaran abnormal terdeteksi pada {$device->name}. Magnitude: {$reading->magnitude}, Status: {$reading->status}",
                'data' => [
                    'vibration_reading_id' => $reading->id,
                    'magnitude' => $reading->magnitude,
                    'threshold' => $reading->threshold,
                    'status' => $reading->status,
                    'axes' => [
                        'x' => $reading->x_axis,
                        'y' => $reading->y_axis,
                        'z' => $reading->z_axis
                    ]
                ],
                'is_read' => false
            ]);

            // Kirim notifikasi (implementasi sesuai kebutuhan)
            $this->sendNotification($alert, $reading);

            Log::info("Abnormal vibration alert created", [
                'alert_id' => $alert->id,
                'device_id' => $reading->device_id,
                'magnitude' => $reading->magnitude,
                'status' => $reading->status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle abnormal vibration: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi menggunakan NotificationService
     */
    private function sendNotification(Alert $alert, VibrationReading $reading): void
    {
        try {
            $notificationService = new NotificationService();
            $success = $notificationService->sendVibrationAlert($alert, $reading);
            
            if ($success) {
                Log::info("Vibration notification sent successfully", [
                    'alert_id' => $alert->id,
                    'device_name' => $reading->device->name,
                    'magnitude' => $reading->magnitude,
                    'status' => $reading->status
                ]);
            } else {
                Log::warning("Vibration notification failed", [
                    'alert_id' => $alert->id,
                    'device_id' => $reading->device_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Notification service error: ' . $e->getMessage());
        }
    }
}
