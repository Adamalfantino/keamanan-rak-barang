<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sensor;
use App\Models\Device;
use Carbon\Carbon;

class SensorSeeder extends Seeder
{
    public function run(): void
    {
        $device = Device::where('device_id', 'RACK_A_001')->first();

        if (!$device) return;

        $sensors = [
            [
                'device_id' => $device->id,
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
            ],
            [
                'device_id' => $device->id,
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
            ],
            [
                'device_id' => $device->id,
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
            ],
        ];

        foreach ($sensors as $sensor) {
            Sensor::firstOrCreate(
                [
                    'device_id' => $sensor['device_id'],
                    'type' => $sensor['type']
                ],
                $sensor
            );
        }
    }
}
