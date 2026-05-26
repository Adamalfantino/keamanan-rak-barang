<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoorAccessReading;
use App\Models\Device;
use App\Models\Alert;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DoorAccessController extends Controller
{
    /**
     * Terima data sensor Reed Switch (Door Access) dari IoT device
     *
     * Mendukung dua format payload:
     * 1. Format ESP32 LoRa raw: { node_id, gateway_id, type, door_opened, device_id }
     * 2. Format MQTT buildPayload: { device_id, node, type, door }
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Normalisasi payload dari ESP32
            $input = $this->normalizeEsp32Payload($request->all());

            // Validasi input
            $validator = Validator::make($input, [
                'device_id' => 'nullable|integer',
                'door_opened' => 'required|boolean',
                'access_method' => 'nullable|string|in:keycard,manual,force,emergency,maintenance,unknown',
                'user_id_card' => 'nullable|string|max:50',
                'duration_seconds' => 'nullable|integer|min:0',
                'door_location' => 'nullable|string|in:front_door,back_door,side_door,main_entrance,rack',
                'is_forced_entry' => 'nullable|boolean',
                'door_opened_at' => 'nullable|date',
                'door_closed_at' => 'nullable|date',
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

            $recordedAt = now();
            
            // Hitung durasi jika ada door_opened_at dan door_closed_at
            $durationSeconds = $data['duration_seconds'] ?? 0;
            if (isset($data['door_opened_at']) && isset($data['door_closed_at'])) {
                $openedAt = Carbon::parse($data['door_opened_at']);
                $closedAt = Carbon::parse($data['door_closed_at']);
                $durationSeconds = $openedAt->diffInSeconds($closedAt);
            }
            
            // Cek apakah dalam jam kerja
            $isAuthorizedTime = $this->checkAuthorizedTime($recordedAt);
            
            // Tentukan apakah akses sah
            $isAuthorizedAccess = $this->determineAuthorizedAccess(
                $data['door_opened'],
                $data['access_method'] ?? 'unknown',
                $data['user_id_card'] ?? null,
                $isAuthorizedTime,
                $data['is_forced_entry'] ?? false
            );
            
            // Tentukan apakah akses mencurigakan
            $isSuspicious = $this->determineSuspiciousAccess(
                $data['door_opened'],
                $isAuthorizedAccess,
                $durationSeconds,
                $data['access_method'] ?? 'unknown',
                $data['user_id_card'] ?? null,
                $data['is_forced_entry'] ?? false
            );
            
            // Tentukan tipe akses
            $accessType = $this->determineAccessType(
                $isAuthorizedAccess,
                $isAuthorizedTime,
                $data['access_method'] ?? 'unknown',
                $data['is_forced_entry'] ?? false
            );

            // Simpan data Door Access
            $doorReading = DoorAccessReading::create([
                'device_id' => $data['device_id'],
                'door_opened' => $data['door_opened'],
                'is_authorized_access' => $isAuthorizedAccess,
                'access_type' => $accessType,
                'access_method' => $data['access_method'] ?? 'unknown',
                'user_id_card' => $data['user_id_card'] ?? null,
                'duration_seconds' => $durationSeconds,
                'is_suspicious' => $isSuspicious,
                'door_location' => $data['door_location'] ?? 'main_entrance',
                'is_forced_entry' => $data['is_forced_entry'] ?? false,
                'metadata' => $data['metadata'] ?? null,
                'door_opened_at' => $data['door_opened_at'] ?? null,
                'door_closed_at' => $data['door_closed_at'] ?? null,
                'recorded_at' => $recordedAt
            ]);

            // Tandai device sebagai online
            Device::markOnline($data['device_id']);

            // Jika akses mencurigakan atau tidak sah, buat alert dan kirim notifikasi
            $alertSent = false;
            if ($isSuspicious || !$isAuthorizedAccess || $accessType === 'forced_entry') {
                $this->handleSuspiciousAccess($doorReading);
                $alertSent = true;
            }

            return response()->json([
                'success' => true,
                'message' => 'Door access data received successfully',
                'data' => [
                    'id' => $doorReading->id,
                    'door_opened' => $doorReading->door_opened,
                    'access_type' => $accessType,
                    'is_authorized' => $isAuthorizedAccess,
                    'is_suspicious' => $isSuspicious,
                    'alert_sent' => $alertSent,
                    'priority' => $doorReading->getPriorityLevel(),
                    'recorded_at' => $recordedAt->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Door access data receive error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process door access data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data door access terbaru
     */
    public function getLatestReadings(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $limit = $request->get('limit', 50);
            $accessType = $request->get('access_type');
            $doorLocation = $request->get('door_location');
            $suspicious = $request->get('suspicious');

            $query = DoorAccessReading::with('device')
                ->orderBy('recorded_at', 'desc');

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            if ($accessType) {
                $query->where('access_type', $accessType);
            }

            if ($doorLocation) {
                $query->where('door_location', $doorLocation);
            }

            if ($suspicious !== null) {
                $query->where('is_suspicious', $suspicious === 'true');
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
                'message' => 'Failed to fetch door access data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil statistik door access
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $hours = $request->get('hours', 24);

            $query = DoorAccessReading::where('recorded_at', '>=', now()->subHours($hours));

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $stats = [
                'total_access_attempts' => $query->count(),
                'door_opened_count' => $query->where('door_opened', true)->count(),
                'authorized_count' => $query->where('is_authorized_access', true)->count(),
                'unauthorized_count' => $query->where('is_authorized_access', false)->count(),
                'suspicious_count' => $query->where('is_suspicious', true)->count(),
                'forced_entry_count' => $query->where('is_forced_entry', true)->count(),
                'avg_duration' => $query->where('door_opened', true)->avg('duration_seconds'),
                'max_duration' => $query->max('duration_seconds'),
                'latest_access' => $query->orderBy('recorded_at', 'desc')->first(),
                'access_types' => $this->getAccessTypeStatistics($query),
                'door_locations' => $this->getDoorLocationStatistics($query),
                'access_methods' => $this->getAccessMethodStatistics($query)
            ];

            // Hitung persentase
            if ($stats['total_access_attempts'] > 0) {
                $stats['authorized_percentage'] = round(($stats['authorized_count'] / $stats['total_access_attempts']) * 100, 2);
                $stats['suspicious_percentage'] = round(($stats['suspicious_count'] / $stats['total_access_attempts']) * 100, 2);
            } else {
                $stats['authorized_percentage'] = 0;
                $stats['suspicious_percentage'] = 0;
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
     * ESP32 LoRa raw: { node_id, gateway_id, type, door_opened, device_id }
     * ESP32 MQTT buildPayload: { device_id, node, type, door }
     */
    private function normalizeEsp32Payload(array $input): array
    {
        // Normalisasi door_opened: bisa dari "door" (MQTT buildPayload)
        if (!isset($input['door_opened']) && isset($input['door'])) {
            $input['door_opened'] = $input['door'];
        }
        // Atau dari "door_open" (format lain)
        if (!isset($input['door_opened']) && isset($input['door_open'])) {
            $input['door_opened'] = $input['door_open'];
        }

        // Pastikan boolean
        if (isset($input['door_opened'])) {
            $input['door_opened'] = filter_var($input['door_opened'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($input['is_forced_entry'])) {
            $input['is_forced_entry'] = filter_var($input['is_forced_entry'], FILTER_VALIDATE_BOOLEAN);
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
     * Cek apakah waktu dalam jam kerja
     */
    private function checkAuthorizedTime(Carbon $timestamp): bool
    {
        // Jam kerja: Senin-Jumat 07:00-18:00
        $workStart = 7;  // 07:00
        $workEnd = 18;   // 18:00
        
        $hour = $timestamp->hour;
        $isWeekday = $timestamp->isWeekday();
        
        return $isWeekday && $hour >= $workStart && $hour < $workEnd;
    }

    /**
     * Tentukan apakah akses sah
     */
    private function determineAuthorizedAccess(bool $doorOpened, string $accessMethod, ?string $userIdCard, bool $isAuthorizedTime, bool $isForcedEntry): bool
    {
        if ($isForcedEntry) {
            return false;
        }
        
        if ($accessMethod === 'emergency') {
            return true; // Emergency access selalu dianggap sah
        }
        
        if ($accessMethod === 'keycard' && $userIdCard && $this->isValidIdCard($userIdCard)) {
            return true;
        }
        
        if ($accessMethod === 'maintenance' && $isAuthorizedTime) {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan apakah akses mencurigakan
     */
    private function determineSuspiciousAccess(bool $doorOpened, bool $isAuthorized, int $duration, string $accessMethod, ?string $userIdCard, bool $isForcedEntry): bool
    {
        if ($isForcedEntry) {
            return true;
        }
        
        if (!$isAuthorized && $accessMethod !== 'emergency') {
            return true;
        }
        
        if ($duration > 180) { // Pintu terbuka lebih dari 3 menit
            return true;
        }
        
        if ($doorOpened && !$userIdCard && $accessMethod === 'manual') {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan tipe akses
     */
    private function determineAccessType(bool $isAuthorized, bool $isAuthorizedTime, string $accessMethod, bool $isForcedEntry): string
    {
        if ($isForcedEntry) {
            return 'forced_entry';
        }
        
        if ($accessMethod === 'emergency') {
            return 'emergency';
        }
        
        if ($accessMethod === 'maintenance') {
            return 'maintenance';
        }
        
        if ($isAuthorized && $isAuthorizedTime) {
            return 'authorized';
        }
        
        if (!$isAuthorizedTime) {
            return 'after_hours';
        }
        
        return 'unauthorized';
    }

    /**
     * Validasi ID card (simulasi)
     */
    private function isValidIdCard(string $idCard): bool
    {
        // Format: EMP-XXXX (4 digit angka)
        return preg_match('/^EMP-\d{4}$/', $idCard);
    }

    /**
     * Handle akses mencurigakan - buat alert dan kirim notifikasi
     */
    private function handleSuspiciousAccess(DoorAccessReading $reading): void
    {
        try {
            $device = $reading->device;
            
            // Tentukan priority berdasarkan tipe akses
            $priority = match($reading->access_type) {
                'forced_entry' => 'critical',
                'unauthorized' => 'high',
                'after_hours' => 'high',
                'emergency' => 'medium',
                'maintenance' => 'low',
                default => 'medium'
            };
            
            // Buat alert
            $alert = Alert::create([
                'device_id' => $reading->device_id,
                'type' => 'door_access_alert',
                'priority' => $priority,
                'title' => 'Akses Pintu Mencurigakan Terdeteksi',
                'message' => $this->generateAlertMessage($reading, $device),
                'data' => [
                    'door_reading_id' => $reading->id,
                    'access_type' => $reading->access_type,
                    'access_method' => $reading->access_method,
                    'user_id_card' => $reading->user_id_card,
                    'duration' => $reading->duration_seconds,
                    'door_location' => $reading->door_location,
                    'is_forced_entry' => $reading->is_forced_entry,
                    'is_authorized_access' => $reading->is_authorized_access
                ],
                'is_read' => false
            ]);

            // Kirim notifikasi
            $this->sendNotification($alert, $reading);

            Log::info("Suspicious door access alert created", [
                'alert_id' => $alert->id,
                'device_id' => $reading->device_id,
                'access_type' => $reading->access_type,
                'user_id_card' => $reading->user_id_card
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle suspicious door access: ' . $e->getMessage());
        }
    }

    /**
     * Generate pesan alert berdasarkan data door access
     */
    private function generateAlertMessage(DoorAccessReading $reading, Device $device): string
    {
        $locationInfo = $reading->door_location ? " di {$reading->door_location}" : '';
        $userInfo = $reading->user_id_card ? " oleh {$reading->user_id_card}" : ' tanpa ID card';
        $methodInfo = $reading->access_method !== 'unknown' ? " menggunakan {$reading->access_method}" : '';
        
        if ($reading->is_forced_entry) {
            return "PAKSA MASUK terdeteksi pada {$device->name}{$locationInfo}. Durasi: {$reading->duration_seconds} detik.";
        }
        
        return "Akses {$reading->access_type} terdeteksi pada {$device->name}{$locationInfo}{$userInfo}{$methodInfo}. " .
               "Durasi: {$reading->duration_seconds} detik.";
    }

    /**
     * Kirim notifikasi menggunakan NotificationService
     */
    private function sendNotification(Alert $alert, DoorAccessReading $reading): void
    {
        try {
            $notificationService = new NotificationService();
            $success = $notificationService->sendDoorAccessAlert($alert, $reading);
            
            if ($success) {
                Log::info("Door access notification sent successfully", [
                    'alert_id' => $alert->id,
                    'device_name' => $reading->device->name,
                    'access_type' => $reading->access_type
                ]);
            } else {
                Log::warning("Door access notification failed", [
                    'alert_id' => $alert->id,
                    'device_id' => $reading->device_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Door access notification service error: ' . $e->getMessage());
        }
    }

    /**
     * Statistik per tipe akses
     */
    private function getAccessTypeStatistics($query): array
    {
        $types = ['authorized', 'unauthorized', 'after_hours', 'forced_entry', 'emergency', 'maintenance'];
        $typeStats = [];
        
        foreach ($types as $type) {
            $typeStats[$type] = $query->where('access_type', $type)->count();
        }
        
        return $typeStats;
    }

    /**
     * Statistik per lokasi pintu
     */
    private function getDoorLocationStatistics($query): array
    {
        $locations = ['front_door', 'back_door', 'side_door', 'main_entrance'];
        $locationStats = [];
        
        foreach ($locations as $location) {
            $locationStats[$location] = $query->where('door_location', $location)->count();
        }
        
        return $locationStats;
    }

    /**
     * Statistik per metode akses
     */
    private function getAccessMethodStatistics($query): array
    {
        $methods = ['keycard', 'manual', 'force', 'emergency', 'maintenance', 'unknown'];
        $methodStats = [];
        
        foreach ($methods as $method) {
            $methodStats[$method] = $query->where('access_method', $method)->count();
        }
        
        return $methodStats;
    }
}
