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
        Schema::create('pir_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->boolean('motion_detected')->default(false); // Gerakan terdeteksi
            $table->integer('motion_intensity')->default(0); // Intensitas gerakan (0-100)
            $table->integer('duration_seconds')->default(0); // Durasi gerakan dalam detik
            $table->boolean('is_authorized_time')->default(true); // Apakah dalam jam kerja
            $table->boolean('is_suspicious')->default(false); // Gerakan mencurigakan
            $table->string('motion_type')->default('normal'); // normal, suspicious, unauthorized
            $table->string('detection_zone')->nullable(); // Area deteksi (front, back, side)
            $table->json('metadata')->nullable(); // Data tambahan sensor
            $table->timestamp('motion_start')->nullable(); // Waktu mulai gerakan
            $table->timestamp('motion_end')->nullable(); // Waktu selesai gerakan
            $table->timestamp('recorded_at'); // Waktu pembacaan sensor
            $table->timestamps();
            
            $table->index(['device_id', 'recorded_at']);
            $table->index(['motion_detected', 'is_suspicious']);
            $table->index(['is_authorized_time', 'motion_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pir_readings');
    }
};
