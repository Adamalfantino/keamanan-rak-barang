<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReedSwitchReading;
use App\Models\Device;
use App\Models\Alert;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReedSwitchController extends Controller
{
    /**
     * Terima data sensor Reed Switch dari IoT device
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|exists:devices,id',
                'door_open' => 'required|boolean',
                'access_method' => 'nullable|string|in:key,card,biometric,remote,force,unknown',
                'door_location' => 'nullable|string|in:front,back,side,main,emergency',
                'open_duration_seconds' => 'nullable|integer|min:0',
                'door_opened_at' => 'nullable|date',
                'door_closed_at' => 'nullable|date|after:door_opened_at',
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
            $recordedAt = now();
            
            // Cek apakah dalam jam kerja
            $isAuthorizedTime = $this->checkAuthorizedTime($recordedAt);
            
            // Set default values
            $accessMethod = $data['access_method'] ?? 'unknown';
            $doorLocation = $data['door_location'] ?? 'main';
            
            // Hitung durasi jika ada door_opened_at dan door_closed_at
            $openDuration = $data['open_duration_seconds'] ?? 0;
            if (isset($data['door_opened_at']) && isset($data['door_closed_at'])) {
                $opened = Carbon::parse($data['door_opened_at']);
                $closed = Carbon::parse($data['door_closed_at']);
                $openDuration = $opened->diffInSeconds($closed);
            } elseif (isset($data['door_opened_at']) && $data['door_open']) {
                // Pintu masih terbuka, hitung dari sekarang
                $opened = Carbon::parse($data['door_opened_at']);
                $openDuration = $opened->diffInSeconds($recordedAt);
            }
            
            // Deteksi pembukaan paksa
            $isForcedEntry = $this->detectForcedEntry(
                $data['door_open'],
                $accessMethod,
                $openDuration,
                $isAuthorizedTime
            );
            
            // Tentukan apakah akses tidak sah
            $isUnauthorized = $this->determineUnauthorizedAccess(
                $data['door_open'],
                $isAuthorizedTime,
                $isForcedEntry,
                $accessMethod,
                $openDuration
            );
            
            // Tentukan level akses
            $accessLevel = $this->determineAccessLevel(
                $isForcedEntry,
                $isAuthorizedTime,
                $accessMethod,
                $openDuration
            );
            
            // Tentukan status pintu
            $doorStatus = $this->determineDoorStatus(
                $data['door_open'],
                $isForcedEntry,
                $openDuration
            );

            // Simpan data Reed Switch
            $reedReading = ReedSwitchReading::create([
                'device_id' => $data['device_id'],
                'door_open' => $data['door_open'],
                'is_authorized' => !$isUnauthorized,
                'is_forced_entry' => $isForcedEntry,
                'access_method' => $accessMethod,
                'door_status' => $doorStatus,
                'open_duration_seconds' => $openDuration,
                'access_level' => $accessLevel,
                'door_location' => $doorLocation,
                'metadata' => $data['metadata'] ?? null,
                'door_opened_at' => $data['door_opened_at'] ?? null,
                'door_closed_at' => $data['door_closed_at'] ?? null,
                'recorded_at' => $recordedAt
            ]);

            // Jika akses tidak sah atau pembukaan paksa, buat alert dan kirim notifikasi
            $alertSent = false;
            if ($isUnauthorized || $isForcedEntry || $accessLevel !== 'normal') {
                $this->handleUnauthorizedAccess($reedReading);
                $alertSent = true;
            }

            return response()->json([
                'success' => true,
                'message' => 'Reed Switch data received successfully',
                'data' => [
                    'id' => $reedReading->id,
                    'door_open' => $reedReading->door_open,
                    'door_status' => $doorStatus,
                    'access_level' => $accessLevel,
                    'is_authorized' => !$isUnauthorized,
                    'is_forced_entry' => $isForcedEntry,
                    'alert_sent' => $alertSent,
                    'recorded_at' => $recordedAt->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Reed Switch data receive error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Reed Switch data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data Reed Switch terbaru
     */
    public function getLatestReadings(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $limit = $request->get('limit', 50);
            $accessLevel = $request->get('access_level');
            $doorLocation = $request->get('door_location');
            $doorStatus = $request->get('door_status');

            $query = ReedSwitchReading::with('device')
                ->orderBy('recorded_at', 'desc');

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            if ($accessLevel) {
                $query->where('access_level', $accessLevel);
            }

            if ($doorLocation) {
                $query->where('door_location', $doorLocation);
            }

            if ($doorStatus) {
                $query->where('door_status', $doorStatus);
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
                'message' => 'Failed to fetch Reed Switch data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil statistik akses pintu
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $hours = $request->get('hours', 24);

            $query = ReedSwitchReading::where('recorded_at', '>=', now()->subHours($hours));

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $stats = [
                'total_readings' => $query->count(),
                'door_open_count' => $query->where('door_open', true)->count(),
                'unauthorized_count' => $query->where('is_authorized', false)->count(),
                'forced_entry_count' => $query->where('is_forced_entry', true)->count(),
                'normal_access_count' => $query->where('access_level', 'normal')->count(),
                'suspicious_access_count' => $query->where('access_level', 'suspicious')->count(),
                'emergency_access_count' => $query->where('access_level', 'emergency')->count(),
                'avg_open_duration' => $query->where('door_open', true)->avg('open_duration_seconds'),
                'max_open_duration' => $query->max('open_duration_seconds'),
                'latest_access' => $query->where('door_open', true)->orderBy('recorded_at', 'desc')->first(),
                'access_methods' => $this->getAccessMethodStatistics($query),
                'door_locations' => $this->getDoorLocationStatistics($query),
                'hourly_access' => $this->getHourlyAccessStatistics($query)
            ];

            // Hitung persentase
            if ($stats['total_readings'] > 0) {
                $stats['door_open_percentage'] = round(($stats['door_open_count'] / $stats['total_readings']) * 100, 2);
                $stats['unauthorized_percentage'] = round(($stats['unauthorized_count'] / $stats['total_readings']) * 100, 2);
                $stats['forced_entry_percentage'] = round(($stats['forced_entry_count'] / $stats['total_readings']) * 100, 2);
            } else {
                $stats['door_open_percentage'] = 0;
                $stats['unauthorized_percentage'] = 0;
                $stats['forced_entry_percentage'] = 0;
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
     * Cek apakah waktu dalam jam kerja
     */
    private function checkAuthorizedTime(Carbon $timestamp): bool
    {
        // Jam kerja: Senin-Jumat 07:00-18:00 (lebih fleksibel untuk akses pintu)
        $workStart = 7;  // 07:00
        $workEnd = 18;   // 18:00
        
        $hour = $timestamp->hour;
        $isWeekday = $timestamp->isWeekday();
        
        return $isWeekday && $hour >= $workStart && $hour < $workEnd;
    }

    /**
     * Deteksi pembukaan paksa
     */
    private function detectForcedEntry(bool $doorOpen, string $accessMethod, int $openDuration, bool $isAuthorizedTime): bool
    {
        if (!$doorOpen) {
            return false;
        }

        // Pembukaan paksa jika:
        // 1. Metode akses adalah 'force'
        // 2. Metode akses tidak dikenal di luar jam kerja
        // 3. Pembukaan sangat cepat di luar jam kerja (hit and run)
        
        if ($accessMethod === 'force') {
            return true;
        }
        
        if ($accessMethod === 'unknown' && !$isAuthorizedTime) {
            return true;
        }
        
        if (!$isAuthorizedTime && $openDuration > 0 && $openDuration < 30) {
            return true; // Pembukaan cepat di luar jam kerja
        }
        
        return false;
    }

    /**
     * Tentukan apakah akses tidak sah
     */
    private function determineUnauthorizedAccess(bool $doorOpen, bool $isAuthorizedTime, bool $isForcedEntry, string $accessMethod, int $openDuration): bool
    {
        if (!$doorOpen) {
            return false;
        }

        // Akses tidak sah jika:
        // 1. Pembukaan paksa
        // 2. Di luar jam kerja (kecuali emergency access)
        // 3. Durasi terbuka terlalu lama (>30 menit)
        // 4. Metode akses tidak valid
        
        if ($isForcedEntry) {
            return true;
        }
        
        if (!$isAuthorizedTime && $accessMethod !== 'emergency') {
            return true;
        }
        
        if ($openDuration > 1800) { // 30 menit
            return true;
        }
        
        if ($accessMethod === 'unknown') {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan level akses
     */
    private function determineAccessLevel(bool $isForcedEntry, bool $isAuthorizedTime, string $accessMethod, int $openDuration): string
    {
        if ($isForcedEntry) {
            return 'emergency';
        }
        
        if (!$isAuthorizedTime) {
            return 'unauthorized';
        }
        
        if ($openDuration > 1800 || $accessMethod === 'unknown') {
            return 'suspicious';
        }
        
        return 'normal';
    }

    /**
     * Tentukan status pintu
     */
    private function determineDoorStatus(bool $doorOpen, bool $isForcedEntry, int $openDuration): string
    {
        if ($isForcedEntry) {
            return 'forced';
        }
        
        if (!$doorOpen) {
            return 'closed';
        }
        
        if ($openDuration > 300) { // 5 menit
            return 'ajar'; // Pintu terbuka terlalu lama
        }
        
        return 'open';
    }

    /**
     * Handle akses tidak sah - buat alert dan kirim notifikasi
     */
    private function handleUnauthorizedAccess(ReedSwitchReading $reading): void
    {
        try {
            $device = $reading->device;
            
            // Tentukan priority berdasarkan level akses
            $priority = match($reading->access_level) {
                'emergency' => 'critical',
                'unauthorized' => 'high',
                'suspicious' => 'medium',
                default => 'low'
            };
            
            // Buat alert
            $alert = Alert::create([
                'device_id' => $reading->device_id,
                'type' => 'door_access',
                'priority' => $priority,
                'title' => 'Akses Pintu Tidak Sah Terdeteksi',
                'message' => $this->generateAlertMessage($reading, $device),
                'data' => [
                    'reed_switch_reading_id' => $reading->id,
                    'access_level' => $reading->access_level,
                    'access_method' => $reading->access_method,
                    'door_status' => $reading->door_status,
                    'door_location' => $reading->door_location,
                    'open_duration' => $reading->open_duration_seconds,
                    'is_forced_entry' => $reading->is_forced_entry,
                    'is_authorized' => $reading->is_authorized
                ],
                'is_read' => false
            ]);

            // Kirim notifikasi
            $this->sendNotification($alert, $reading);

            Log::info("Unauthorized door access alert created", [
                'alert_id' => $alert->id,
                'device_id' => $reading->device_id,
                'access_level' => $reading->access_level,
                'door_location' => $reading->door_location
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle unauthorized door access: ' . $e->getMessage());
        }
    }

    /**
     * Generate pesan alert berdasarkan data Reed Switch
     */
    private function generateAlertMessage(ReedSwitchReading $reading, Device $device): string
    {
        $timeInfo = $reading->checkAuthorizedTime() ? 'dalam jam kerja' : 'di luar jam kerja';
        $locationInfo = $reading->door_location ? " pintu {$reading->door_location}" : '';
        $methodInfo = $reading->access_method !== 'unknown' ? " menggunakan {$reading->access_method}" : ' dengan metode tidak dikenal';
        
        $message = "Akses {$reading->access_level} terdeteksi pada {$device->name}{$locationInfo} {$timeInfo}{$methodInfo}.";
        
        if ($reading->is_forced_entry) {
            $message .= " PEMBUKAAN PAKSA TERDETEKSI!";
        }
        
        if ($reading->open_duration_seconds > 300) {
            $message .= " Pintu terbuka selama " . gmdate('H:i:s', $reading->open_duration_seconds) . ".";
        }
        
        return $message;
    }

    /**
     * Kirim notifikasi menggunakan NotificationService
     */
    private function sendNotification(Alert $alert, ReedSwitchReading $reading): void
    {
        try {
            $notificationService = new NotificationService();
            $success = $notificationService->sendReedSwitchAlert($alert, $reading);
            
            if ($success) {
                Log::info("Reed Switch notification sent successfully", [
                    'alert_id' => $alert->id,
                    'device_name' => $reading->device->name,
                    'access_level' => $reading->access_level
                ]);
            } else {
                Log::warning("Reed Switch notification failed", [
                    'alert_id' => $alert->id,
                    'device_id' => $reading->device_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Reed Switch notification service error: ' . $e->getMessage());
        }
    }

    /**
     * Statistik per metode akses
     */
    private function getAccessMethodStatistics($query): array
    {
        $methods = ['key', 'card', 'biometric', 'remote', 'force', 'unknown'];
        $methodStats = [];
        
        foreach ($methods as $method) {
            $methodStats[$method] = $query->where('access_method', $method)->count();
        }
        
        return $methodStats;
    }

    /**
     * Statistik per lokasi pintu
     */
    private function getDoorLocationStatistics($query): array
    {
        $locations = ['front', 'back', 'side', 'main', 'emergency'];
        $locationStats = [];
        
        foreach ($locations as $location) {
            $locationStats[$location] = $query->where('door_location', $location)->count();
        }
        
        return $locationStats;
    }

    /**
     * Statistik akses per jam
     */
    private function getHourlyAccessStatistics($query): array
    {
        $hourlyStats = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $count = $query->whereRaw('HOUR(recorded_at) = ?', [$hour])->count();
            $hourlyStats[sprintf('%02d:00', $hour)] = $count;
        }
        
        return $hourlyStats;
    }
}
