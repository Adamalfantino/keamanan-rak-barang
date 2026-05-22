<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoorReading;
use App\Models\Device;
use App\Models\Alert;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DoorController extends Controller
{
    /**
     * Terima data sensor Reed Switch/Door dari IoT device
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|exists:devices,id',
                'door_open' => 'required|boolean',
                'door_location' => 'nullable|string|in:front_panel,back_panel,side_door,main_door',
                'open_duration_seconds' => 'nullable|integer|min:0',
                'door_opened_at' => 'nullable|date',
                'door_closed_at' => 'nullable|date|after:door_opened_at',
                'access_card_data' => 'nullable|array',
                'proper_closure' => 'nullable|boolean',
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
            
            // Cek apakah dalam jam kerja yang sah
            $isAuthorizedAccess = $this->checkAuthorizedAccess($recordedAt, $data);
            
            // Hitung durasi terbuka
            $openDuration = $this->calculateOpenDuration($data);
            
            // Deteksi pembukaan paksa
            $isForcedEntry = $this->detectForcedEntry($data, $isAuthorizedAccess, $openDuration);
            
            // Tentukan tipe akses
            $accessType = $this->determineAccessType($data['door_open'], $isAuthorizedAccess, $isForcedEntry, $openDuration);
            
            // Tentukan proper closure
            $properClosure = $data['proper_closure'] ?? true;
            if (!$data['door_open'] && !isset($data['proper_closure'])) {
                // Jika pintu ditutup tapi tidak ada info proper_closure, anggap normal
                $properClosure = true;
            }

            // Simpan data door reading
            $doorReading = DoorReading::create([
                'device_id' => $data['device_id'],
                'door_open' => $data['door_open'],
                'is_authorized_access' => $isAuthorizedAccess,
                'is_forced_entry' => $isForcedEntry,
                'access_type' => $accessType,
                'door_location' => $data['door_location'] ?? 'main_door',
                'open_duration_seconds' => $openDuration,
                'proper_closure' => $properClosure,
                'access_card_data' => $data['access_card_data'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'door_opened_at' => $data['door_opened_at'] ?? null,
                'door_closed_at' => $data['door_closed_at'] ?? null,
                'recorded_at' => $recordedAt
            ]);

            // Jika akses tidak sah atau pembukaan paksa, buat alert
            $alertSent = false;
            if (!$isAuthorizedAccess || $isForcedEntry || $accessType === 'unauthorized') {
                $this->handleUnauthorizedAccess($doorReading);
                $alertSent = true;
            }

            return response()->json([
                'success' => true,
                'message' => 'Door sensor data received successfully',
                'data' => [
                    'id' => $doorReading->id,
                    'door_open' => $doorReading->door_open,
                    'access_type' => $accessType,
                    'is_authorized_access' => $isAuthorizedAccess,
                    'is_forced_entry' => $isForcedEntry,
                    'security_risk_level' => $doorReading->getSecurityRiskLevel(),
                    'alert_sent' => $alertSent,
                    'recorded_at' => $recordedAt->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Door sensor data receive error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process door sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data door sensor terbaru
     */
    public function getLatestReadings(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $limit = $request->get('limit', 50);
            $accessType = $request->get('access_type');
            $doorLocation = $request->get('door_location');
            $doorOpen = $request->get('door_open');

            $query = DoorReading::with('device')
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

            if ($doorOpen !== null) {
                $query->where('door_open', $doorOpen === 'true');
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
                'message' => 'Failed to fetch door sensor data',
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

            $query = DoorReading::where('recorded_at', '>=', now()->subHours($hours));

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $stats = [
                'total_readings' => $query->count(),
                'door_opened_count' => $query->where('door_open', true)->count(),
                'authorized_access_count' => $query->where('is_authorized_access', true)->count(),
                'unauthorized_access_count' => $query->where('is_authorized_access', false)->count(),
                'forced_entry_count' => $query->where('is_forced_entry', true)->count(),
                'normal_access_count' => $query->where('access_type', 'normal')->count(),
                'maintenance_access_count' => $query->where('access_type', 'maintenance')->count(),
                'avg_open_duration' => $query->where('door_open', true)->avg('open_duration_seconds'),
                'max_open_duration' => $query->max('open_duration_seconds'),
                'currently_open_doors' => $query->currentlyOpen()->count(),
                'latest_access' => $query->where('door_open', true)->orderBy('recorded_at', 'desc')->first(),
                'door_locations' => $this->getDoorLocationStatistics($query),
                'access_types' => $this->getAccessTypeStatistics($query),
                'security_incidents' => $query->where(function($q) {
                    $q->where('is_forced_entry', true)
                      ->orWhere('is_authorized_access', false);
                })->count()
            ];

            // Hitung persentase
            if ($stats['total_readings'] > 0) {
                $stats['unauthorized_percentage'] = round(($stats['unauthorized_access_count'] / $stats['total_readings']) * 100, 2);
                $stats['security_incident_percentage'] = round(($stats['security_incidents'] / $stats['total_readings']) * 100, 2);
            } else {
                $stats['unauthorized_percentage'] = 0;
                $stats['security_incident_percentage'] = 0;
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
                'period_hours' => $hours
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch door statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doors that are currently open
     */
    public function getCurrentlyOpenDoors(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');

            $query = DoorReading::currentlyOpen()->with('device');

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $openDoors = $query->get();

            return response()->json([
                'success' => true,
                'data' => $openDoors,
                'count' => $openDoors->count(),
                'message' => $openDoors->count() > 0 ? 'Found open doors' : 'All doors are closed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch open doors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek apakah akses dalam jam kerja yang sah
     */
    private function checkAuthorizedAccess(Carbon $timestamp, array $data): bool
    {
        // Jam kerja: Senin-Jumat 07:00-18:00
        $workStart = 7;  // 07:00
        $workEnd = 18;   // 18:00
        
        $hour = $timestamp->hour;
        $isWeekday = $timestamp->isWeekday();
        $isWorkingHours = $isWeekday && $hour >= $workStart && $hour < $workEnd;
        
        // Jika ada data kartu akses, dianggap sah
        if (isset($data['access_card_data']) && !empty($data['access_card_data'])) {
            return true;
        }
        
        // Jika dalam jam kerja, dianggap sah
        return $isWorkingHours;
    }

    /**
     * Hitung durasi terbuka
     */
    private function calculateOpenDuration(array $data): int
    {
        if (isset($data['door_opened_at']) && isset($data['door_closed_at'])) {
            $start = Carbon::parse($data['door_opened_at']);
            $end = Carbon::parse($data['door_closed_at']);
            return $start->diffInSeconds($end);
        }
        
        return $data['open_duration_seconds'] ?? 0;
    }

    /**
     * Deteksi pembukaan paksa
     */
    private function detectForcedEntry(array $data, bool $isAuthorizedAccess, int $openDuration): bool
    {
        // Indikator pembukaan paksa:
        // 1. Tidak ada otorisasi dan tidak ada kartu akses
        // 2. Durasi sangat singkat (<3 detik) - kemungkinan dipaksa lalu ditutup cepat
        // 3. Tidak ditutup dengan benar di luar jam kerja
        
        if (!$isAuthorizedAccess && empty($data['access_card_data'])) {
            return true;
        }
        
        if ($openDuration > 0 && $openDuration < 3) {
            return true;
        }
        
        if (isset($data['proper_closure']) && !$data['proper_closure'] && !$isAuthorizedAccess) {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan tipe akses
     */
    private function determineAccessType(bool $doorOpen, bool $isAuthorizedAccess, bool $isForcedEntry, int $openDuration): string
    {
        if ($isForcedEntry) {
            return 'forced';
        }
        
        if (!$isAuthorizedAccess) {
            return 'unauthorized';
        }
        
        // Maintenance jika terbuka lama (>5 menit) dengan otorisasi
        if ($doorOpen && $openDuration > 300 && $isAuthorizedAccess) {
            return 'maintenance';
        }
        
        return 'normal';
    }

    /**
     * Handle akses tidak sah - buat alert dan kirim notifikasi
     */
    private function handleUnauthorizedAccess(DoorReading $reading): void
    {
        try {
            $device = $reading->device;
            
            // Tentukan priority berdasarkan tipe akses
            $priority = match($reading->access_type) {
                'forced' => 'critical',
                'unauthorized' => 'high',
                default => 'medium'
            };
            
            // Buat alert
            $alert = Alert::create([
                'device_id' => $reading->device_id,
                'type' => 'door_access',
                'priority' => $priority,
                'title' => 'Akses Pintu Tidak Sah Terdeteksi',
                'message' => $this->generateAlertMessage($reading, $device),
                'data' => [
                    'door_reading_id' => $reading->id,
                    'access_type' => $reading->access_type,
                    'door_location' => $reading->door_location,
                    'is_forced_entry' => $reading->is_forced_entry,
                    'open_duration' => $reading->open_duration_seconds,
                    'is_authorized_access' => $reading->is_authorized_access,
                    'security_risk_level' => $reading->getSecurityRiskLevel()
                ],
                'is_read' => false
            ]);

            // Kirim notifikasi
            $this->sendNotification($alert, $reading);

            Log::info("Unauthorized door access alert created", [
                'alert_id' => $alert->id,
                'device_id' => $reading->device_id,
                'access_type' => $reading->access_type,
                'door_location' => $reading->door_location
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle unauthorized door access: ' . $e->getMessage());
        }
    }

    /**
     * Generate pesan alert berdasarkan data door
     */
    private function generateAlertMessage(DoorReading $reading, Device $device): string
    {
        $accessInfo = $reading->is_authorized_access ? 'akses sah' : 'akses tidak sah';
        $locationInfo = $reading->door_location ? " pada {$reading->door_location}" : '';
        $durationInfo = $reading->open_duration_seconds > 0 ? " selama {$reading->open_duration_seconds} detik" : '';
        
        $message = "Akses {$reading->access_type} terdeteksi pada {$device->name}{$locationInfo}. ";
        $message .= "Status: {$accessInfo}{$durationInfo}.";
        
        if ($reading->is_forced_entry) {
            $message .= " PERINGATAN: Indikasi pembukaan paksa!";
        }
        
        return $message;
    }

    /**
     * Kirim notifikasi menggunakan NotificationService
     */
    private function sendNotification(Alert $alert, DoorReading $reading): void
    {
        try {
            $notificationService = new NotificationService();
            $success = $notificationService->sendDoorAlert($alert, $reading);
            
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
            Log::error('Door notification service error: ' . $e->getMessage());
        }
    }

    /**
     * Statistik per lokasi pintu
     */
    private function getDoorLocationStatistics($query): array
    {
        $locations = ['front_panel', 'back_panel', 'side_door', 'main_door'];
        $locationStats = [];
        
        foreach ($locations as $location) {
            $locationStats[$location] = $query->where('door_location', $location)->count();
        }
        
        return $locationStats;
    }

    /**
     * Statistik per tipe akses
     */
    private function getAccessTypeStatistics($query): array
    {
        $types = ['normal', 'unauthorized', 'forced', 'maintenance'];
        $typeStats = [];
        
        foreach ($types as $type) {
            $typeStats[$type] = $query->where('access_type', $type)->count();
        }
        
        return $typeStats;
    }
}
