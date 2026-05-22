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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama device (Device Rak A, Device Rak B, dll)
            $table->string('device_id')->unique(); // ID unik device untuk LoRa
            $table->string('location'); // Lokasi device (Rak A, Rak B, dll)
            $table->enum('type', ['sensor_node', 'gateway', 'server']); // Tipe device
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline'); // Status device
            $table->string('ip_address')->nullable(); // IP address jika ada
            $table->string('mac_address')->nullable(); // MAC address
            $table->integer('signal_strength')->nullable(); // Kekuatan sinyal (0-100)
            $table->timestamp('last_seen')->nullable(); // Terakhir kali device terdeteksi
            $table->json('configuration')->nullable(); // Konfigurasi device dalam JSON
            $table->text('description')->nullable(); // Deskripsi device
            $table->boolean('is_active')->default(true); // Apakah device aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
