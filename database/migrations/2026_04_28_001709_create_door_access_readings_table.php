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
        Schema::create('door_access_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->boolean('door_opened')->default(false); // Status pintu terbuka
            $table->boolean('is_authorized_access')->default(false); // Akses resmi/tidak
            $table->string('access_type')->default('unknown'); // authorized, unauthorized, maintenance, emergency
            $table->string('access_method')->nullable(); // keycard, manual, force, unknown
            $table->string('user_id_card')->nullable(); // ID card yang digunakan (jika ada)
            $table->integer('duration_seconds')->default(0); // Durasi pintu terbuka
            $table->boolean('is_suspicious')->default(false); // Akses mencurigakan
            $table->string('door_location')->nullable(); // front_door, back_door, side_door
            $table->boolean('is_forced_entry')->default(false); // Paksa masuk
            $table->json('metadata')->nullable(); // Data tambahan sensor
            $table->timestamp('door_opened_at')->nullable(); // Waktu pintu dibuka
            $table->timestamp('door_closed_at')->nullable(); // Waktu pintu ditutup
            $table->timestamp('recorded_at'); // Waktu pembacaan sensor
            $table->timestamps();
            
            $table->index(['device_id', 'recorded_at']);
            $table->index(['door_opened', 'is_suspicious']);
            $table->index(['is_authorized_access', 'access_type']);
            $table->index(['user_id_card', 'access_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('door_access_readings');
    }
};
