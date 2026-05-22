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
        Schema::create('reed_switch_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->boolean('door_open')->default(false); // Status pintu (true=buka, false=tutup)
            $table->boolean('is_authorized')->default(true); // Apakah pembukaan sah
            $table->boolean('is_forced_entry')->default(false); // Pembukaan paksa
            $table->string('access_method')->nullable(); // Metode akses (key, card, force, unknown)
            $table->string('door_status')->default('closed'); // closed, open, ajar, forced
            $table->integer('open_duration_seconds')->default(0); // Durasi pintu terbuka
            $table->string('access_level')->default('normal'); // normal, suspicious, unauthorized, emergency
            $table->string('door_location')->nullable(); // Lokasi pintu (front, back, side, main)
            $table->json('metadata')->nullable(); // Data tambahan sensor
            $table->timestamp('door_opened_at')->nullable(); // Waktu pintu dibuka
            $table->timestamp('door_closed_at')->nullable(); // Waktu pintu ditutup
            $table->timestamp('recorded_at'); // Waktu pembacaan sensor
            $table->timestamps();
            
            $table->index(['device_id', 'recorded_at']);
            $table->index(['door_open', 'is_authorized']);
            $table->index(['access_level', 'door_status']);
            $table->index(['is_forced_entry', 'door_location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reed_switch_readings');
    }
};
