<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PirReading;
use App\Models\Device;
use App\Models\Alert;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PirController extends Controller
{
    /**
     * Terima data sensor PIR dari IoT device
     */
    public function receiveData(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'device_id' => 'required|exists:devices,id',
                'motion_detected' => 'required|boolean',
                'motion_intensity' => 'nullable|integer|min:0|max:100',
                'duration_seconds' => 'nullable|integer|min:0',
                'detection_zone' => 'nullable|string|in:front,back,side,center',
                'motion_start' => 'nullable|date',
                'motion_end' => 'nullable|date|after:motion_start',
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
            
            // Tentukan intensitas default jika tidak ada
            $motionIntensity = $data['motion_intensity'] ?? ($data['motion_detected'] ? 50 : 0);
            
            // Hitung durasi jika ada motion_start dan motion_end
            $durationSeconds = $data['duration_seconds'] ?? 0;
            if (isset($data['motion_start']) && isset($data['motion_end'])) {
                $start = Carbon::parse($data['motion_start']);
                $end = Carbon::parse($data['motion_end']);
                $durationSeconds = $start->diffInSeconds($end);
            }
            
            // Tentukan apakah gerakan mencurigakan
            $isSuspicious = $this->determineSuspiciousMotion(
                $data['motion_detected'],
                $motionIntensity,
                $durationSeconds,
                $isAuthorizedTime
            );
            
            // Tentukan tipe gerakan
            $motionType = $this->determineMotionType(
                $data['motion_detected'],
                $isSuspicious,
                $isAuthorizedTime,
                $motionIntensity,
                $durationSeconds
            );

            // Simpan data PIR
            $pirReading = PirReading::create([
                'device_id' => $data['device_id'],
                'motion_detected' => $data['motion_detected'],
                'motion_intensity' => $motionIntensity,
                'duration_seconds' => $durationSeconds,
                'is_authorized_time' => $isAuthorizedTime,
                'is_suspicious' => $isSuspicious,
                'motion_type' => $motionType,
                'detection_zone' => $data['detection_zone'] ?? 'center',
                'metadata' => $data['metadata'] ?? null,
                'motion_start' => $data['motion_start'] ?? null,
                'motion_end' => $data['motion_end'] ?? null,
                'recorded_at' => $recordedAt
            ]);

            // Tandai device sebagai online
            Device::markOnline($data['device_id']);

            // Jika gerakan mencurigakan, buat alert dan kirim notifikasi
            $alertSent = false;
            if ($isSuspicious || $motionType === 'unauthorized') {
                $this->handleSuspiciousMotion($pirReading);
                $alertSent = true;
            }

            return response()->json([
                'success' => true,
                'message' => 'PIR data received successfully',
                'data' => [
                    'id' => $pirReading->id,
                    'motion_detected' => $pirReading->motion_detected,
                    'motion_type' => $motionType,
                    'is_suspicious' => $isSuspicious,
                    'is_authorized_time' => $isAuthorizedTime,
                    'alert_sent' => $alertSent,
                    'recorded_at' => $recordedAt->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('PIR data receive error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process PIR data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data PIR terbaru
     */
    public function getLatestReadings(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $limit = $request->get('limit', 50);
            $motionType = $request->get('motion_type');
            $detectionZone = $request->get('detection_zone');

            $query = PirReading::with('device')
                ->orderBy('recorded_at', 'desc');

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            if ($motionType) {
                $query->where('motion_type', $motionType);
            }

            if ($detectionZone) {
                $query->where('detection_zone', $detectionZone);
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
                'message' => 'Failed to fetch PIR data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil statistik gerakan
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->get('device_id');
            $hours = $request->get('hours', 24);

            $query = PirReading::where('recorded_at', '>=', now()->subHours($hours));

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $stats = [
                'total_readings' => $query->count(),
                'motion_detected_count' => $query->where('motion_detected', true)->count(),
                'suspicious_count' => $query->where('is_suspicious', true)->count(),
                'unauthorized_count' => $query->where('motion_type', 'unauthorized')->count(),
                'normal_count' => $query->where('motion_type', 'normal')->count(),
                'avg_intensity' => $query->where('motion_detected', true)->avg('motion_intensity'),
                'avg_duration' => $query->where('motion_detected', true)->avg('duration_seconds'),
                'max_intensity' => $query->max('motion_intensity'),
                'max_duration' => $query->max('duration_seconds'),
                'latest_motion' => $query->where('motion_detected', true)->orderBy('recorded_at', 'desc')->first(),
                'detection_zones' => $this->getZoneStatistics($query)
            ];

            // Hitung persentase
            if ($stats['total_readings'] > 0) {
                $stats['motion_percentage'] = round(($stats['motion_detected_count'] / $stats['total_readings']) * 100, 2);
                $stats['suspicious_percentage'] = round(($stats['suspicious_count'] / $stats['total_readings']) * 100, 2);
            } else {
                $stats['motion_percentage'] = 0;
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
     * Cek apakah waktu dalam jam kerja
     */
    private function checkAuthorizedTime(Carbon $timestamp): bool
    {
        // Jam kerja: Senin-Jumat 08:00-17:00
        $workStart = 8; // 08:00
        $workEnd = 17;  // 17:00
        
        $hour = $timestamp->hour;
        $isWeekday = $timestamp->isWeekday();
        
        return $isWeekday && $hour >= $workStart && $hour < $workEnd;
    }

    /**
     * Tentukan apakah gerakan mencurigakan
     */
    private function determineSuspiciousMotion(bool $motionDetected, int $intensity, int $duration, bool $isAuthorizedTime): bool
    {
        if (!$motionDetected) {
            return false;
        }

        // Gerakan mencurigakan jika:
        // 1. Di luar jam kerja
        // 2. Intensitas tinggi (>80) 
        // 3. Durasi sangat lama (>300 detik)
        
        if (!$isAuthorizedTime) {
            return true;
        }
        
        if ($intensity > 80) {
            return true;
        }
        
        if ($duration > 300) {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan tipe gerakan
     */
    private function determineMotionType(bool $motionDetected, bool $isSuspicious, bool $isAuthorizedTime, int $intensity, int $duration): string
    {
        if (!$motionDetected) {
            return 'none';
        }
        
        if (!$isAuthorizedTime) {
            return 'unauthorized';
        }
        
        if ($isSuspicious) {
            return 'suspicious';
        }
        
        return 'normal';
    }

    /**
     * Handle gerakan mencurigakan - buat alert dan kirim notifikasi
     */
    private function handleSuspiciousMotion(PirReading $reading): void
    {
        try {
            $device = $reading->device;
            
            // Tentukan priority berdasarkan tipe gerakan
            $priority = match($reading->motion_type) {
                'unauthorized' => 'high',
                'suspicious' => 'medium',
                default => 'low'
            };
            
            // Buat alert
            $alert = Alert::create([
                'device_id' => $reading->device_id,
                'type' => 'motion_detected',
                'priority' => $priority,
                'title' => 'Gerakan Mencurigakan Terdeteksi',
                'message' => $this->generateAlertMessage($reading, $device),
                'data' => [
                    'pir_reading_id' => $reading->id,
                    'motion_type' => $reading->motion_type,
                    'intensity' => $reading->motion_intensity,
                    'duration' => $reading->duration_seconds,
                    'detection_zone' => $reading->detection_zone,
                    'is_authorized_time' => $reading->is_authorized_time
                ],
                'is_read' => false
            ]);

            // Kirim notifikasi
            $this->sendNotification($alert, $reading);

            Log::info("Suspicious motion alert created", [
                'alert_id' => $alert->id,
                'device_id' => $reading->device_id,
                'motion_type' => $reading->motion_type,
                'intensity' => $reading->motion_intensity
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle suspicious motion: ' . $e->getMessage());
        }
    }

    /**
     * Generate pesan alert berdasarkan data PIR
     */
    private function generateAlertMessage(PirReading $reading, Device $device): string
    {
        $timeInfo = $reading->is_authorized_time ? 'dalam jam kerja' : 'di luar jam kerja';
        $zoneInfo = $reading->detection_zone ? " di zona {$reading->detection_zone}" : '';
        
        return "Gerakan {$reading->motion_type} terdeteksi pada {$device->name}{$zoneInfo} {$timeInfo}. " .
               "Intensitas: {$reading->motion_intensity}%, Durasi: {$reading->duration_seconds} detik.";
    }

    /**
     * Kirim notifikasi menggunakan NotificationService
     */
    private function sendNotification(Alert $alert, PirReading $reading): void
    {
        try {
            $notificationService = new NotificationService();
            $success = $notificationService->sendPirAlert($alert, $reading);
            
            if ($success) {
                Log::info("PIR notification sent successfully", [
                    'alert_id' => $alert->id,
                    'device_name' => $reading->device->name,
                    'motion_type' => $reading->motion_type
                ]);
            } else {
                Log::warning("PIR notification failed", [
                    'alert_id' => $alert->id,
                    'device_id' => $reading->device_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('PIR notification service error: ' . $e->getMessage());
        }
    }

    /**
     * Statistik per zona deteksi
     */
    private function getZoneStatistics($query): array
    {
        $zones = ['front', 'back', 'side', 'center'];
        $zoneStats = [];
        
        foreach ($zones as $zone) {
            $zoneStats[$zone] = $query->where('detection_zone', $zone)->count();
        }
        
        return $zoneStats;
    }
}
