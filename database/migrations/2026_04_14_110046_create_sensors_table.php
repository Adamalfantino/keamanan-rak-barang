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
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade'); // Relasi ke tabel devices
            $table->string('name'); // Nama sensor (PIR, SW-420, Reed Switch)
            $table->enum('type', ['pir', 'vibration', 'reed_switch', 'temperature', 'humidity']); // Tipe sensor
            $table->string('pin_number')->nullable(); // Pin GPIO yang digunakan
            $table->enum('status', ['active', 'inactive', 'error'])->default('active'); // Status sensor
            $table->decimal('threshold_min', 8, 2)->nullable(); // Nilai minimum threshold
            $table->decimal('threshold_max', 8, 2)->nullable(); // Nilai maksimum threshold
            $table->string('unit')->nullable(); // Unit pengukuran (%, °C, V, dll)
            $table->integer('sampling_rate')->default(1000); // Rate sampling dalam ms
            $table->json('calibration_data')->nullable(); // Data kalibrasi sensor
            $table->text('description')->nullable(); // Deskripsi sensor
            $table->boolean('is_active')->default(true); // Apakah sensor aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};
