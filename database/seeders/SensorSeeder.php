<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sensors = [
            // Sensors untuk Device Rak A (device_id = 1)
            [
                'device_id' => 1,
                'name' => 'PIR Motion Sensor',
                'type' => 'pir',
                'pin_number' => 'GPIO2',
                'status' => 'active',
                'threshold_min' => 0,
                'threshold_max' => 1,
                'unit' => 'boolean',
                'sampling_rate' => 500,
                'calibration_data' => json_encode([
                    'sensitivity' => 'high',
                    'detection_range' => '7m',
                    'detection_angle' => '120°'
                ]),
                'description' => 'Sensor PIR untuk mendeteksi gerakan manusia di area rak',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'device_id' => 1,
                'name' => 'Vibration Sensor SW-420',
                'type' => 'vibration',
                'pin_number' => 'GPIO3',
                'status' => 'active',
                'threshold_min' => 0,
                'threshold_max' => 1024,
                'unit' => 'analog',
                'sampling_rate' => 100,
                'calibration_data' => json_encode([
                    'sensitivity' => 'medium',
                    'trigger_threshold' => 512,
                    'debounce_time' => '50ms'
                ]),
                'description' => 'Sensor getaran SW-420 untuk mendeteksi guncangan pada rak',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'device_id' => 1,
                'name' => 'Reed Switch',
                'type' => 'reed_switch',
                'pin_number' => 'GPIO4',
                'status' => 'active',
                'threshold_min' => 0,
                'threshold_max' => 1,
                'unit' => 'boolean',
                'sampling_rate' => 1000,
                'calibration_data' => json_encode([
                    'magnet_distance' => '2cm',
                    'switch_type' => 'normally_open'
                ]),
                'description' => 'Reed switch untuk mendeteksi status buka/tutup rak',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('sensors')->insert($sensors);
    }
}
