<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use Carbon\Carbon;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            [
                'name' => 'Device Rak A',
                'device_id' => 'RACK_A_001',
                'location' => 'Rak A - Lantai 1',
                'type' => 'sensor_node',
                'status' => 'online',
                'ip_address' => '192.168.1.101',
                'mac_address' => '00:1B:44:11:3A:B7',
                'signal_strength' => 95,
                'last_seen' => Carbon::now()->subSeconds(5),
                'configuration' => json_encode([
                    'lora_frequency' => '868MHz',
                    'transmission_power' => '14dBm',
                    'data_rate' => 'SF7BW125',
                    'battery_type' => 'Li-ion 3.7V'
                ]),
                'description' => 'Node sensor utama untuk monitoring rak dengan sensor PIR, getaran, dan reed switch',
                'is_active' => true,
            ],
            [
                'name' => 'LoRa Gateway',
                'device_id' => 'GATEWAY_001',
                'location' => 'Server Room',
                'type' => 'gateway',
                'status' => 'online',
                'ip_address' => '192.168.1.100',
                'mac_address' => '00:1B:44:11:3A:B9',
                'signal_strength' => 100,
                'last_seen' => Carbon::now()->subSeconds(1),
                'configuration' => json_encode([
                    'lora_frequency' => '868MHz',
                    'channels' => 8,
                    'max_devices' => 100,
                    'range' => '5km'
                ]),
                'description' => 'Gateway LoRa untuk menerima data dari semua sensor node',
                'is_active' => true,
            ],
            [
                'name' => 'Server Monitoring',
                'device_id' => 'SERVER_001',
                'location' => 'Server Room',
                'type' => 'server',
                'status' => 'online',
                'ip_address' => '192.168.1.10',
                'mac_address' => '00:1B:44:11:3A:BA',
                'signal_strength' => 100,
                'last_seen' => Carbon::now(),
                'configuration' => json_encode([
                    'os' => 'Ubuntu 22.04 LTS',
                    'cpu' => 'Intel Core i5-8400',
                    'ram' => '16GB DDR4',
                    'storage' => '500GB SSD'
                ]),
                'description' => 'Server utama untuk processing dan storage data monitoring',
                'is_active' => true,
            ],
        ];

        foreach ($devices as $device) {
            Device::firstOrCreate(
                ['device_id' => $device['device_id']],
                $device
            );
        }
    }
}
