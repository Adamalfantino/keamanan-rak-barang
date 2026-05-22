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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->onDelete('cascade'); // Relasi ke tabel sensors
            $table->foreignId('device_id')->constrained()->onDelete('cascade'); // Relasi ke tabel devices
            $table->decimal('value', 10, 4); // Nilai pembacaan sensor
            $table->string('raw_value')->nullable(); // Nilai mentah dari sensor
            $table->enum('status', ['normal', 'warning', 'critical', 'error'])->default('normal'); // Status pembacaan
            $table->decimal('battery_level', 5, 2)->nullable(); // Level baterai device (%)
            $table->integer('signal_strength')->nullable(); // Kekuatan sinyal saat pembacaan
            $table->json('metadata')->nullable(); // Data tambahan dalam JSON
            $table->timestamp('reading_time'); // Waktu pembacaan sensor
            $table->boolean('is_processed')->default(false); // Apakah data sudah diproses
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['sensor_id', 'reading_time']);
            $table->index(['device_id', 'reading_time']);
            $table->index('reading_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
