<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat admin hanya jika belum ada
        if (!\App\Models\User::where('email', 'admin@smartrack.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin Smart Rack',
                'email' => 'admin@smartrack.com',
                'password' => 'admin123',
            ]);
        }

        // Seed Smart Rack Security data
        $this->call([
            DeviceSeeder::class,
            SensorSeeder::class,
            SampleDataSeeder::class,
        ]);

        $this->command->info('Smart Rack Security database seeded successfully!');
    }
}
