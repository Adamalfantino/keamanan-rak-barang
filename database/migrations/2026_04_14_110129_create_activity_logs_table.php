<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null'); // Device yang terkait
            $table->foreignId('sensor_id')->nullable()->constrained()->onDelete('set null'); // Sensor yang terkait
            $table->string('event_type'); // Tipe kejadian (motion_detected, door_opened, vibration_detected, dll)
            $table->enum('severity', ['info', 'warning', 'critical', 'error'])->default('info'); // Tingkat keparahan
            $table->string('title'); // Judul aktivitas
            $table->text('description'); // Deskripsi detail aktivitas
            $table->json('event_data')->nullable(); // Data kejadian dalam JSON
            $table->string('location')->nullable(); // Lokasi kejadian
            $table->string('user_agent')->nullable(); // User agent jika dari web
            $table->string('ip_address')->nullable(); // IP address sumber
            $table->timestamp('event_time'); // Waktu kejadian
            $table->boolean('is_acknowledged')->default(false); // Apakah sudah diakui
            $table->timestamp('acknowledged_at')->nullable(); // Waktu diakui
            $table->string('acknowledged_by')->nullable(); // Siapa yang mengakui
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['event_type', 'event_time']);
            $table->index(['severity', 'event_time']);
            $table->index('event_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
