<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah data sudah ada, kalau sudah skip
        if (\App\Models\Sensor::count() === 0) {
            return;
        }
        // Sample Sensor Readings
        $sensorReadings = [
            // PIR Sensor readings (normal state)
            [
                'sensor_id' => 1, // PIR A
                'device_id' => 1,
                'value' => 0,
                'raw_value' => '0',
                'status' => 'normal',
                'battery_level' => 85.5,
                'signal_strength' => 95,
                'metadata' => json_encode(['temperature' => 25.3, 'humidity' => 60]),
                'reading_time' => Carbon::now()->subMinutes(1),
                'is_processed' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Vibration sensor readings (normal state)
            [
                'sensor_id' => 2, // Vibration A
                'device_id' => 1,
                'value' => 45,
                'raw_value' => '45',
                'status' => 'normal',
                'battery_level' => 85.5,
                'signal_strength' => 95,
                'metadata' => json_encode(['baseline' => 40, 'peak' => 50]),
                'reading_time' => Carbon::now()->subMinutes(1),
                'is_processed' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Reed switch readings (closed state)
            [
                'sensor_id' => 3, // Reed Switch A
                'device_id' => 1,
                'value' => 0,
                'raw_value' => '0',
                'status' => 'normal',
                'battery_level' => 85.5,
                'signal_strength' => 95,
                'metadata' => json_encode(['door_state' => 'closed']),
                'reading_time' => Carbon::now()->subMinutes(1),
                'is_processed' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Sample Activity Logs
        $activityLogs = [
            [
                'device_id' => 1,
                'sensor_id' => 1,
                'event_type' => 'motion_detected',
                'severity' => 'warning',
                'title' => 'Gerakan Terdeteksi',
                'description' => 'Sensor PIR mendeteksi gerakan manusia di area rak A pada pukul 12:10',
                'event_data' => json_encode([
                    'sensor_value' => 1,
                    'duration' => '5 seconds',
                    'confidence' => 'high'
                ]),
                'location' => 'Rak A - Lantai 1',
                'user_agent' => null,
                'ip_address' => '192.168.1.101',
                'event_time' => Carbon::now()->subHours(2)->subMinutes(10),
                'is_acknowledged' => false,
                'acknowledged_at' => null,
                'acknowledged_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'device_id' => 1,
                'sensor_id' => 3,
                'event_type' => 'door_opened',
                'severity' => 'info',
                'title' => 'Rak Dibuka',
                'description' => 'Reed switch mendeteksi rak A dibuka pada pukul 12:15',
                'event_data' => json_encode([
                    'sensor_value' => 1,
                    'previous_state' => 'closed',
                    'current_state' => 'opened'
                ]),
                'location' => 'Rak A - Lantai 1',
                'user_agent' => null,
                'ip_address' => '192.168.1.101',
                'event_time' => Carbon::now()->subHours(2)->subMinutes(5),
                'is_acknowledged' => true,
                'acknowledged_at' => Carbon::now()->subHours(2),
                'acknowledged_by' => 'admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'device_id' => 1,
                'sensor_id' => 2,
                'event_type' => 'system_normal',
                'severity' => 'info',
                'title' => 'Sistem Normal',
                'description' => 'Semua sensor kembali ke kondisi normal pada pukil 12:20',
                'event_data' => json_encode([
                    'all_sensors_status' => 'normal',
                    'system_health' => 'good'
                ]),
                'location' => 'Rak A - Lantai 1',
                'user_agent' => null,
                'ip_address' => '192.168.1.101',
                'event_time' => Carbon::now()->subHours(2),
                'is_acknowledged' => true,
                'acknowledged_at' => Carbon::now()->subHours(1)->subMinutes(30),
                'acknowledged_by' => 'admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Sample Alerts
        $alerts = [
            [
                'device_id' => 1,
                'sensor_id' => 1,
                'activity_log_id' => 1,
                'alert_type' => 'security_breach',
                'priority' => 'high',
                'status' => 'acknowledged',
                'title' => 'Aktivitas Mencurigakan Terdeteksi',
                'message' => 'Sensor PIR mendeteksi gerakan di area rak A di luar jam operasional',
                'alert_data' => json_encode([
                    'detection_time' => '12:10:00',
                    'sensor_confidence' => 'high',
                    'recommended_action' => 'Check CCTV footage'
                ]),
                'location' => 'Rak A - Lantai 1',
                'triggered_at' => Carbon::now()->subHours(2)->subMinutes(10),
                'acknowledged_at' => Carbon::now()->subHours(2)->subMinutes(5),
                'resolved_at' => null,
                'acknowledged_by' => 'admin',
                'resolved_by' => null,
                'resolution_notes' => null,
                'is_sent_notification' => true,
                'notification_channels' => json_encode(['email', 'dashboard']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ];

        DB::table('sensor_readings')->insert($sensorReadings);
        DB::table('activity_logs')->insert($activityLogs);
        DB::table('alerts')->insert($alerts);
    }
}
