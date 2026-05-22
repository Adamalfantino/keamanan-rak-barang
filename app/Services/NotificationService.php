<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\VibrationReading;
use App\Models\PirReading;
use App\Models\DoorAccessReading;
use App\Models\ReedSwitchReading;
use App\Models\DoorReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Kirim notifikasi untuk getaran abnormal
     */
    public function sendVibrationAlert(Alert $alert, VibrationReading $reading): bool
    {
        try {
            $device = $reading->device;
            
            // Prepare notification data
            $notificationData = [
                'device_name' => $device->name,
                'device_location' => $device->location,
                'magnitude' => round($reading->magnitude, 2),
                'threshold' => $reading->threshold,
                'status' => $reading->status,
                'timestamp' => $reading->recorded_at->format('Y-m-d H:i:s'),
                'axes' => [
                    'x' => round($reading->x_axis, 2),
                    'y' => round($reading->y_axis, 2),
                    'z' => round($reading->z_axis, 2)
                ]
            ];

            // Kirim berbagai jenis notifikasi
            $results = [];
            
            // 1. Email notification
            $results['email'] = $this->sendEmailNotification($notificationData);
            
            // 2. SMS notification (jika diperlukan)
            // $results['sms'] = $this->sendSmsNotification($notificationData);
            
            // 3. Push notification
            $results['push'] = $this->sendPushNotification($notificationData);
            
            // 4. Webhook notification
            $results['webhook'] = $this->sendWebhookNotification($notificationData);
            
            // 5. Log notification
            $this->logNotification($notificationData);

            return true;

        } catch (\Exception $e) {
            Log::error('Notification service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notification
     */
    private function sendEmailNotification(array $data): bool
    {
        try {
            // Implementasi email notification
            // Untuk sementara hanya log
            Log::info('📧 Email notification would be sent', $data);
            
            // TODO: Implementasi actual email
            /*
            Mail::send('emails.vibration-alert', $data, function($message) use ($data) {
                $message->to('admin@smartrack.com')
                       ->subject('🚨 Getaran Abnormal - ' . $data['device_name']);
            });
            */
            
            return true;
        } catch (\Exception $e) {
            Log::error('Email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim SMS notification
     */
    private function sendSmsNotification(array $data): bool
    {
        try {
            $message = "🚨 ALERT: Getaran abnormal pada {$data['device_name']}. " .
                      "Magnitude: {$data['magnitude']} (Normal: <{$data['threshold']}). " .
                      "Status: {$data['status']}. Waktu: {$data['timestamp']}";

            // Implementasi SMS (Twilio, Nexmo, dll)
            Log::info('📱 SMS notification would be sent', [
                'message' => $message,
                'device' => $data['device_name']
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim push notification
     */
    private function sendPushNotification(array $data): bool
    {
        try {
            $payload = [
                'title' => '🚨 Getaran Abnormal Terdeteksi',
                'body' => "Getaran tidak normal pada {$data['device_name']}. Magnitude: {$data['magnitude']}",
                'data' => $data,
                'priority' => $data['status'] === 'critical' ? 'high' : 'normal'
            ];

            // Implementasi push notification (FCM, OneSignal, dll)
            Log::info('🔔 Push notification would be sent', $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim webhook notification
     */
    private function sendWebhookNotification(array $data): bool
    {
        try {
            $webhookUrl = config('app.vibration_webhook_url');
            
            if (!$webhookUrl) {
                return true; // Skip jika tidak ada webhook URL
            }

            $payload = [
                'event' => 'vibration_abnormal',
                'timestamp' => now()->toISOString(),
                'data' => $data
            ];

            // Kirim HTTP POST ke webhook
            // Implementasi dengan Guzzle atau cURL
            Log::info('🔗 Webhook notification would be sent', [
                'url' => $webhookUrl,
                'payload' => $payload
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Webhook notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log notification untuk audit trail
     */
    private function logNotification(array $data): void
    {
        Log::channel('vibration')->info('🚨 Vibration Alert Notification', [
            'device' => $data['device_name'],
            'location' => $data['device_location'],
            'magnitude' => $data['magnitude'],
            'status' => $data['status'],
            'timestamp' => $data['timestamp'],
            'axes' => $data['axes']
        ]);
    }

    /**
     * Kirim notifikasi untuk gerakan PIR mencurigakan
     */
    public function sendPirAlert(Alert $alert, PirReading $reading): bool
    {
        try {
            $device = $reading->device;
            
            // Prepare notification data
            $notificationData = [
                'device_name' => $device->name,
                'device_location' => $device->location,
                'motion_type' => $reading->motion_type,
                'motion_intensity' => $reading->motion_intensity,
                'duration_seconds' => $reading->duration_seconds,
                'detection_zone' => $reading->detection_zone,
                'is_authorized_time' => $reading->is_authorized_time,
                'timestamp' => $reading->recorded_at->format('Y-m-d H:i:s'),
                'priority' => $alert->priority,
                'alert_message' => $alert->message
            ];

            // Kirim berbagai jenis notifikasi
            $results = [];
            
            // 1. Email notification
            $results['email'] = $this->sendPirEmailNotification($notificationData);
            
            // 2. SMS notification untuk alert high priority
            if ($alert->priority === 'high') {
                $results['sms'] = $this->sendPirSmsNotification($notificationData);
            }
            
            // 3. Push notification
            $results['push'] = $this->sendPirPushNotification($notificationData);
            
            // 4. Webhook notification
            $results['webhook'] = $this->sendPirWebhookNotification($notificationData);
            
            // 5. Log notification
            $this->logPirNotification($notificationData);

            return true;

        } catch (\Exception $e) {
            Log::error('PIR notification service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notification untuk PIR
     */
    private function sendPirEmailNotification(array $data): bool
    {
        try {
            $subject = "🚨 Alert PIR - {$data['motion_type']} - {$data['device_name']}";
            
            Log::info('📧 PIR Email notification would be sent', [
                'subject' => $subject,
                'device' => $data['device_name'],
                'motion_type' => $data['motion_type'],
                'priority' => $data['priority']
            ]);
            
            // TODO: Implementasi actual email
            /*
            Mail::send('emails.pir-alert', $data, function($message) use ($data, $subject) {
                $message->to('security@smartrack.com')
                       ->subject($subject);
            });
            */
            
            return true;
        } catch (\Exception $e) {
            Log::error('PIR email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim SMS notification untuk PIR
     */
    private function sendPirSmsNotification(array $data): bool
    {
        try {
            $timeInfo = $data['is_authorized_time'] ? 'jam kerja' : 'luar jam kerja';
            $zoneInfo = $data['detection_zone'] ? " zona {$data['detection_zone']}" : '';
            
            $message = "🚨 SECURITY ALERT: Gerakan {$data['motion_type']} pada {$data['device_name']}{$zoneInfo} " .
                      "({$timeInfo}). Intensitas: {$data['motion_intensity']}%. Durasi: {$data['duration_seconds']}s. " .
                      "Waktu: {$data['timestamp']}";

            Log::info('📱 PIR SMS notification would be sent', [
                'message' => $message,
                'device' => $data['device_name'],
                'motion_type' => $data['motion_type']
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('PIR SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim push notification untuk PIR
     */
    private function sendPirPushNotification(array $data): bool
    {
        try {
            $title = match($data['motion_type']) {
                'unauthorized' => '🚨 Akses Tidak Sah Terdeteksi',
                'suspicious' => '⚠️ Gerakan Mencurigakan',
                default => '👁️ Gerakan Terdeteksi'
            };
            
            $timeInfo = $data['is_authorized_time'] ? 'dalam jam kerja' : 'di luar jam kerja';
            $body = "Gerakan {$data['motion_type']} pada {$data['device_name']} {$timeInfo}";
            
            $payload = [
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => $data['priority'] === 'high' ? 'high' : 'normal',
                'sound' => $data['motion_type'] === 'unauthorized' ? 'alarm' : 'default'
            ];

            Log::info('🔔 PIR Push notification would be sent', $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error('PIR push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim webhook notification untuk PIR
     */
    private function sendPirWebhookNotification(array $data): bool
    {
        try {
            $webhookUrl = config('app.pir_webhook_url');
            
            if (!$webhookUrl) {
                return true; // Skip jika tidak ada webhook URL
            }

            $payload = [
                'event' => 'pir_motion_detected',
                'motion_type' => $data['motion_type'],
                'priority' => $data['priority'],
                'timestamp' => now()->toISOString(),
                'data' => $data
            ];

            Log::info('🔗 PIR Webhook notification would be sent', [
                'url' => $webhookUrl,
                'payload' => $payload
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('PIR webhook notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log PIR notification untuk audit trail
     */
    private function logPirNotification(array $data): void
    {
        Log::channel('security')->info('🚨 PIR Motion Alert Notification', [
            'device' => $data['device_name'],
            'location' => $data['device_location'],
            'motion_type' => $data['motion_type'],
            'intensity' => $data['motion_intensity'],
            'duration' => $data['duration_seconds'],
            'detection_zone' => $data['detection_zone'],
            'authorized_time' => $data['is_authorized_time'],
            'timestamp' => $data['timestamp'],
            'priority' => $data['priority']
        ]);
    }

    /**
     * Kirim notifikasi test
     */
    public function sendTestNotification(): array
    {
        $testData = [
            'device_name' => 'Test Device',
            'device_location' => 'Test Location',
            'magnitude' => 3.5,
            'threshold' => 2.0,
            'status' => 'warning',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'axes' => ['x' => 1.2, 'y' => 2.1, 'z' => 2.8]
        ];

        return [
            'email' => $this->sendEmailNotification($testData),
            'push' => $this->sendPushNotification($testData),
            'webhook' => $this->sendWebhookNotification($testData)
        ];
    }

    /**
     * Kirim notifikasi untuk akses Reed Switch tidak sah
     */
    public function sendReedSwitchAlert(Alert $alert, ReedSwitchReading $reading): bool
    {
        try {
            $device = $reading->device;
            
            // Prepare notification data
            $notificationData = [
                'device_name' => $device->name,
                'device_location' => $device->location,
                'access_level' => $reading->access_level,
                'access_method' => $reading->access_method,
                'door_status' => $reading->door_status,
                'door_location' => $reading->door_location,
                'open_duration_seconds' => $reading->open_duration_seconds,
                'is_forced_entry' => $reading->is_forced_entry,
                'is_authorized' => $reading->is_authorized,
                'timestamp' => $reading->recorded_at->format('Y-m-d H:i:s'),
                'priority' => $alert->priority,
                'alert_message' => $alert->message
            ];

            // Kirim berbagai jenis notifikasi
            $results = [];
            
            // 1. Email notification
            $results['email'] = $this->sendReedSwitchEmailNotification($notificationData);
            
            // 2. SMS notification untuk alert critical/high priority
            if (in_array($alert->priority, ['critical', 'high'])) {
                $results['sms'] = $this->sendReedSwitchSmsNotification($notificationData);
            }
            
            // 3. Push notification
            $results['push'] = $this->sendReedSwitchPushNotification($notificationData);
            
            // 4. Webhook notification
            $results['webhook'] = $this->sendReedSwitchWebhookNotification($notificationData);
            
            // 5. Log notification
            $this->logReedSwitchNotification($notificationData);

            return true;

        } catch (\Exception $e) {
            Log::error('Reed Switch notification service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notification untuk Reed Switch
     */
    private function sendReedSwitchEmailNotification(array $data): bool
    {
        try {
            $accessType = $data['is_forced_entry'] ? 'PEMBUKAAN PAKSA' : strtoupper($data['access_level']);
            $subject = "🚨 Alert Reed Switch - {$accessType} - {$data['device_name']}";
            
            Log::info('📧 Reed Switch Email notification would be sent', [
                'subject' => $subject,
                'device' => $data['device_name'],
                'access_level' => $data['access_level'],
                'door_location' => $data['door_location'],
                'priority' => $data['priority']
            ]);
            
            // TODO: Implementasi actual email
            /*
            Mail::send('emails.reed-switch-alert', $data, function($message) use ($data, $subject) {
                $message->to('security@smartrack.com')
                       ->subject($subject);
            });
            */
            
            return true;
        } catch (\Exception $e) {
            Log::error('Reed Switch email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim SMS notification untuk Reed Switch
     */
    private function sendReedSwitchSmsNotification(array $data): bool
    {
        try {
            $locationInfo = $data['door_location'] ? " pintu {$data['door_location']}" : '';
            $methodInfo = $data['access_method'] !== 'unknown' ? " ({$data['access_method']})" : ' (metode tidak dikenal)';
            
            if ($data['is_forced_entry']) {
                $message = "🚨 PEMBUKAAN PAKSA! {$data['device_name']}{$locationInfo}{$methodInfo}. " .
                          "Waktu: {$data['timestamp']}. Segera periksa lokasi!";
            } else {
                $durationInfo = $data['open_duration_seconds'] > 0 ? 
                    " Durasi: " . gmdate('H:i:s', $data['open_duration_seconds']) : '';
                
                $message = "🚨 AKSES TIDAK SAH: {$data['access_level']} pada {$data['device_name']}{$locationInfo}{$methodInfo}.{$durationInfo} " .
                          "Waktu: {$data['timestamp']}";
            }

            Log::info('📱 Reed Switch SMS notification would be sent', [
                'message' => $message,
                'device' => $data['device_name'],
                'access_level' => $data['access_level']
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Reed Switch SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim push notification untuk Reed Switch
     */
    private function sendReedSwitchPushNotification(array $data): bool
    {
        try {
            if ($data['is_forced_entry']) {
                $title = '🚨 PEMBUKAAN PAKSA TERDETEKSI!';
                $body = "Pembukaan paksa pada {$data['device_name']}";
                $sound = 'emergency';
            } else {
                $title = match($data['access_level']) {
                    'emergency' => '🚨 Akses Darurat Terdeteksi',
                    'unauthorized' => '⛔ Akses Tidak Sah',
                    'suspicious' => '⚠️ Akses Mencurigakan',
                    default => '🚪 Akses Pintu'
                };
                
                $locationInfo = $data['door_location'] ? " pintu {$data['door_location']}" : '';
                $body = "Akses {$data['access_level']} pada {$data['device_name']}{$locationInfo}";
                $sound = $data['access_level'] === 'emergency' ? 'emergency' : 'alert';
            }
            
            $payload = [
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => $data['priority'] === 'critical' ? 'high' : 'normal',
                'sound' => $sound,
                'category' => 'door_access'
            ];

            Log::info('🔔 Reed Switch Push notification would be sent', $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Reed Switch push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim webhook notification untuk Reed Switch
     */
    private function sendReedSwitchWebhookNotification(array $data): bool
    {
        try {
            $webhookUrl = config('app.reed_switch_webhook_url');
            
            if (!$webhookUrl) {
                return true; // Skip jika tidak ada webhook URL
            }

            $payload = [
                'event' => 'door_access_detected',
                'access_level' => $data['access_level'],
                'is_forced_entry' => $data['is_forced_entry'],
                'priority' => $data['priority'],
                'timestamp' => now()->toISOString(),
                'data' => $data
            ];

            Log::info('🔗 Reed Switch Webhook notification would be sent', [
                'url' => $webhookUrl,
                'payload' => $payload
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Reed Switch webhook notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log Reed Switch notification untuk audit trail
     */
    private function logReedSwitchNotification(array $data): void
    {
        Log::channel('security')->info('🚨 Reed Switch Door Access Alert', [
            'device' => $data['device_name'],
            'location' => $data['device_location'],
            'access_level' => $data['access_level'],
            'access_method' => $data['access_method'],
            'door_status' => $data['door_status'],
            'door_location' => $data['door_location'],
            'open_duration' => $data['open_duration_seconds'],
            'is_forced_entry' => $data['is_forced_entry'],
            'is_authorized' => $data['is_authorized'],
            'timestamp' => $data['timestamp'],
            'priority' => $data['priority']
        ]);
    }

    /**
     * Kirim notifikasi untuk akses pintu tidak sah
     */
    public function sendDoorAlert(Alert $alert, DoorReading $reading): bool
    {
        try {
            $device = $reading->device;
            
            // Prepare notification data
            $notificationData = [
                'device_name' => $device->name,
                'device_location' => $device->location,
                'access_type' => $reading->access_type,
                'door_location' => $reading->door_location,
                'door_open' => $reading->door_open,
                'is_authorized_access' => $reading->is_authorized_access,
                'is_forced_entry' => $reading->is_forced_entry,
                'open_duration_seconds' => $reading->open_duration_seconds,
                'proper_closure' => $reading->proper_closure,
                'security_risk_level' => $reading->getSecurityRiskLevel(),
                'timestamp' => $reading->recorded_at->format('Y-m-d H:i:s'),
                'priority' => $alert->priority,
                'alert_message' => $alert->message,
                'access_card_used' => !empty($reading->access_card_data)
            ];

            // Kirim berbagai jenis notifikasi
            $results = [];
            
            // 1. Email notification
            $results['email'] = $this->sendDoorEmailNotification($notificationData);
            
            // 2. SMS notification untuk alert critical/high priority
            if (in_array($alert->priority, ['critical', 'high'])) {
                $results['sms'] = $this->sendDoorSmsNotification($notificationData);
            }
            
            // 3. Push notification
            $results['push'] = $this->sendDoorPushNotification($notificationData);
            
            // 4. Webhook notification
            $results['webhook'] = $this->sendDoorWebhookNotification($notificationData);
            
            // 5. Log notification
            $this->logDoorNotification($notificationData);

            return true;

        } catch (\Exception $e) {
            Log::error('Door notification service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notification untuk Door/Reed Switch
     */
    private function sendDoorEmailNotification(array $data): bool
    {
        try {
            $subject = "🚨 Security Alert - {$data['access_type']} - {$data['device_name']}";
            
            if ($data['is_forced_entry']) {
                $subject = "🔴 CRITICAL: Forced Entry - {$data['device_name']}";
            }
            
            Log::info('📧 Door Email notification would be sent', [
                'subject' => $subject,
                'device' => $data['device_name'],
                'access_type' => $data['access_type'],
                'door_location' => $data['door_location'],
                'priority' => $data['priority']
            ]);
            
            // TODO: Implementasi actual email
            /*
            Mail::send('emails.door-alert', $data, function($message) use ($data, $subject) {
                $message->to('security@smartrack.com')
                       ->subject($subject);
            });
            */
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim SMS notification untuk Door/Reed Switch
     */
    private function sendDoorSmsNotification(array $data): bool
    {
        try {
            $accessInfo = $data['is_authorized_access'] ? 'sah' : 'TIDAK SAH';
            $locationInfo = $data['door_location'] ? " {$data['door_location']}" : '';
            $durationInfo = $data['open_duration_seconds'] > 0 ? " ({$data['open_duration_seconds']}s)" : '';
            
            $message = "🚨 SECURITY: Akses {$data['access_type']} pada {$data['device_name']}{$locationInfo}. " .
                      "Status: {$accessInfo}{$durationInfo}. Waktu: {$data['timestamp']}";
            
            if ($data['is_forced_entry']) {
                $message = "🔴 CRITICAL: PEMBUKAAN PAKSA pada {$data['device_name']}{$locationInfo}! " .
                          "Segera periksa lokasi. Waktu: {$data['timestamp']}";
            }

            Log::info('📱 Door SMS notification would be sent', [
                'message' => $message,
                'device' => $data['device_name'],
                'access_type' => $data['access_type']
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim push notification untuk Door/Reed Switch
     */
    private function sendDoorPushNotification(array $data): bool
    {
        try {
            $title = match($data['access_type']) {
                'forced' => '🔴 Pembukaan Paksa Terdeteksi!',
                'unauthorized' => '🚨 Akses Tidak Sah',
                'maintenance' => '🔧 Akses Maintenance',
                default => '🚪 Akses Pintu'
            };
            
            $accessInfo = $data['is_authorized_access'] ? 'sah' : 'tidak sah';
            $locationInfo = $data['door_location'] ? " pada {$data['door_location']}" : '';
            $body = "Akses {$accessInfo} terdeteksi pada {$data['device_name']}{$locationInfo}";
            
            $payload = [
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => $data['priority'] === 'critical' ? 'high' : 'normal',
                'sound' => $data['is_forced_entry'] ? 'emergency' : 'alert',
                'category' => 'security_door_access'
            ];

            Log::info('🔔 Door Push notification would be sent', $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim webhook notification untuk Door/Reed Switch
     */
    private function sendDoorWebhookNotification(array $data): bool
    {
        try {
            $webhookUrl = config('app.door_webhook_url');
            
            if (!$webhookUrl) {
                return true; // Skip jika tidak ada webhook URL
            }

            $payload = [
                'event' => 'door_access_detected',
                'access_type' => $data['access_type'],
                'security_level' => $data['security_risk_level'],
                'priority' => $data['priority'],
                'timestamp' => now()->toISOString(),
                'data' => $data
            ];

            Log::info('🔗 Door Webhook notification would be sent', [
                'url' => $webhookUrl,
                'payload' => $payload
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door webhook notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log Door notification untuk audit trail
     */
    private function logDoorNotification(array $data): void
    {
        Log::channel('security')->info('🚪 Door Access Alert Notification', [
            'device' => $data['device_name'],
            'location' => $data['device_location'],
            'door_location' => $data['door_location'],
            'access_type' => $data['access_type'],
            'authorized' => $data['is_authorized_access'],
            'forced_entry' => $data['is_forced_entry'],
            'duration' => $data['open_duration_seconds'],
            'security_risk' => $data['security_risk_level'],
            'access_card_used' => $data['access_card_used'],
            'timestamp' => $data['timestamp'],
            'priority' => $data['priority']
        ]);
    }

    /**
     * Kirim notifikasi test untuk PIR
     */
    public function sendTestPirNotification(): array
    {
        $testData = [
            'device_name' => 'Test PIR Device',
            'device_location' => 'Test Location',
            'motion_type' => 'suspicious',
            'motion_intensity' => 85,
            'duration_seconds' => 120,
            'detection_zone' => 'front',
            'is_authorized_time' => false,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'priority' => 'high',
            'alert_message' => 'Test PIR alert message'
        ];

        return [
            'email' => $this->sendPirEmailNotification($testData),
            'sms' => $this->sendPirSmsNotification($testData),
            'push' => $this->sendPirPushNotification($testData),
            'webhook' => $this->sendPirWebhookNotification($testData)
        ];
    }

    /**
     * Kirim notifikasi test untuk Reed Switch
     */
    public function sendTestReedSwitchNotification(): array
    {
        $testData = [
            'device_name' => 'Test Reed Switch Device',
            'device_location' => 'Test Location',
            'access_level' => 'unauthorized',
            'access_method' => 'force',
            'door_status' => 'forced',
            'door_location' => 'main',
            'open_duration_seconds' => 180,
            'is_forced_entry' => true,
            'is_authorized' => false,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'priority' => 'critical',
            'alert_message' => 'Test Reed Switch alert message'
        ];

        return [
            'email' => $this->sendReedSwitchEmailNotification($testData),
            'sms' => $this->sendReedSwitchSmsNotification($testData),
            'push' => $this->sendReedSwitchPushNotification($testData),
            'webhook' => $this->sendReedSwitchWebhookNotification($testData)
        ];
    }

    /**

     * Kirim notifikasi untuk akses pintu mencurigakan
     */
    public function sendDoorAccessAlert(Alert $alert, DoorAccessReading $reading): bool
    {
        try {
            $device = $reading->device;
            
            // Prepare notification data
            $notificationData = [
                'device_name' => $device->name,
                'device_location' => $device->location,
                'access_type' => $reading->access_type,
                'access_method' => $reading->access_method,
                'user_id_card' => $reading->user_id_card,
                'duration_seconds' => $reading->duration_seconds,
                'door_location' => $reading->door_location,
                'is_forced_entry' => $reading->is_forced_entry,
                'is_authorized_access' => $reading->is_authorized_access,
                'timestamp' => $reading->recorded_at->format('Y-m-d H:i:s'),
                'priority' => $alert->priority,
                'alert_message' => $alert->message
            ];

            // Kirim berbagai jenis notifikasi
            $results = [];
            
            // 1. Email notification
            $results['email'] = $this->sendDoorAccessEmailNotification($notificationData);
            
            // 2. SMS notification untuk alert critical/high priority
            if (in_array($alert->priority, ['critical', 'high'])) {
                $results['sms'] = $this->sendDoorAccessSmsNotification($notificationData);
            }
            
            // 3. Push notification
            $results['push'] = $this->sendDoorAccessPushNotification($notificationData);
            
            // 4. Webhook notification
            $results['webhook'] = $this->sendDoorAccessWebhookNotification($notificationData);
            
            // 5. Log notification
            $this->logDoorAccessNotification($notificationData);

            return true;

        } catch (\Exception $e) {
            Log::error('Door access notification service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim email notification untuk Door Access
     */
    private function sendDoorAccessEmailNotification(array $data): bool
    {
        try {
            $accessTypeText = match($data['access_type']) {
                'forced_entry' => 'PAKSA MASUK',
                'unauthorized' => 'AKSES TIDAK SAH',
                'after_hours' => 'AKSES LUAR JAM KERJA',
                'emergency' => 'AKSES DARURAT',
                'maintenance' => 'AKSES MAINTENANCE',
                default => 'AKSES MENCURIGAKAN'
            };
            
            $subject = "🚨 Alert Door Access - {$accessTypeText} - {$data['device_name']}";
            
            Log::info('📧 Door Access Email notification would be sent', [
                'subject' => $subject,
                'device' => $data['device_name'],
                'access_type' => $data['access_type'],
                'user_id_card' => $data['user_id_card'],
                'priority' => $data['priority']
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door access email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim SMS notification untuk Door Access
     */
    private function sendDoorAccessSmsNotification(array $data): bool
    {
        try {
            $locationInfo = $data['door_location'] ? " di {$data['door_location']}" : '';
            $userInfo = $data['user_id_card'] ? " oleh {$data['user_id_card']}" : ' tanpa ID';
            
            if ($data['is_forced_entry']) {
                $message = "🚨 SECURITY BREACH: PAKSA MASUK pada {$data['device_name']}{$locationInfo}. " .
                          "Durasi: {$data['duration_seconds']}s. Waktu: {$data['timestamp']}";
            } else {
                $message = "🚨 DOOR ALERT: Akses {$data['access_type']} pada {$data['device_name']}{$locationInfo}{$userInfo}. " .
                          "Metode: {$data['access_method']}. Durasi: {$data['duration_seconds']}s. Waktu: {$data['timestamp']}";
            }

            Log::info('📱 Door Access SMS notification would be sent', [
                'message' => $message,
                'device' => $data['device_name'],
                'access_type' => $data['access_type']
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door access SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim push notification untuk Door Access
     */
    private function sendDoorAccessPushNotification(array $data): bool
    {
        try {
            $title = match($data['access_type']) {
                'forced_entry' => '🚨 PAKSA MASUK TERDETEKSI',
                'unauthorized' => '⚠️ Akses Tidak Sah',
                'after_hours' => '🌙 Akses Luar Jam Kerja',
                'emergency' => '🆘 Akses Darurat',
                'maintenance' => '🔧 Akses Maintenance',
                default => '🚪 Akses Pintu Terdeteksi'
            };
            
            $locationInfo = $data['door_location'] ? " di {$data['door_location']}" : '';
            $userInfo = $data['user_id_card'] ? " oleh {$data['user_id_card']}" : '';
            $body = "Akses pada {$data['device_name']}{$locationInfo}{$userInfo}";
            
            $payload = [
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => $data['priority'] === 'critical' ? 'high' : 'normal',
                'sound' => $data['is_forced_entry'] ? 'alarm' : 'default',
                'category' => 'door_access'
            ];

            Log::info('🔔 Door Access Push notification would be sent', $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door access push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim webhook notification untuk Door Access
     */
    private function sendDoorAccessWebhookNotification(array $data): bool
    {
        try {
            $webhookUrl = config('app.door_access_webhook_url');
            
            if (!$webhookUrl) {
                return true; // Skip jika tidak ada webhook URL
            }

            $payload = [
                'event' => 'door_access_alert',
                'access_type' => $data['access_type'],
                'priority' => $data['priority'],
                'is_forced_entry' => $data['is_forced_entry'],
                'timestamp' => now()->toISOString(),
                'data' => $data
            ];

            Log::info('🔗 Door Access Webhook notification would be sent', [
                'url' => $webhookUrl,
                'payload' => $payload
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Door access webhook notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log Door Access notification untuk audit trail
     */
    private function logDoorAccessNotification(array $data): void
    {
        Log::channel('security')->info('🚨 Door Access Alert Notification', [
            'device' => $data['device_name'],
            'location' => $data['device_location'],
            'access_type' => $data['access_type'],
            'access_method' => $data['access_method'],
            'user_id_card' => $data['user_id_card'],
            'duration' => $data['duration_seconds'],
            'door_location' => $data['door_location'],
            'is_forced_entry' => $data['is_forced_entry'],
            'authorized' => $data['is_authorized_access'],
            'timestamp' => $data['timestamp'],
            'priority' => $data['priority']
        ]);
    }

    /**
     * Kirim notifikasi test untuk Door Access
     */
    public function sendTestDoorAccessNotification(): array
    {
        $testData = [
            'device_name' => 'Test Door Access Device',
            'device_location' => 'Test Location',
            'access_type' => 'unauthorized',
            'access_method' => 'force',
            'user_id_card' => null,
            'duration_seconds' => 45,
            'door_location' => 'front_door',
            'is_forced_entry' => true,
            'is_authorized_access' => false,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'priority' => 'critical',
            'alert_message' => 'Test door access alert message'
        ];

        return [
            'email' => $this->sendDoorAccessEmailNotification($testData),
            'sms' => $this->sendDoorAccessSmsNotification($testData),
            'push' => $this->sendDoorAccessPushNotification($testData),
            'webhook' => $this->sendDoorAccessWebhookNotification($testData)
        ];
    }
}