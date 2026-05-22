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
        Schema::create('door_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->boolean('door_open')->default(false); // Status pintu terbuka
            $table->boolean('is_authorized_access')->default(true); // Akses yang sah
            $table->boolean('is_forced_entry')->default(false); // Pembukaan paksa
            $table->string('access_type')->default('normal'); // normal, unauthorized, forced, maintenance
            $table->string('door_location')->nullable(); // front_panel, back_panel, side_door, main_door
            $table->integer('open_duration_seconds')->default(0); // Durasi terbuka dalam detik
            $table->boolean('proper_closure')->default(true); // Apakah ditutup dengan benar
            $table->json('access_card_data')->nullable(); // Data kartu akses jika ada
            $table->json('metadata')->nullable(); // Data tambahan sensor
            $table->timestamp('door_opened_at')->nullable(); // Waktu pintu dibuka
            $table->timestamp('door_closed_at')->nullable(); // Waktu pintu ditutup
            $table->timestamp('recorded_at'); // Waktu pembacaan sensor
            $table->timestamps();
            
            $table->index(['device_id', 'recorded_at']);
            $table->index(['door_open', 'is_authorized_access']);
            $table->index(['access_type', 'door_location']);
            $table->index(['is_forced_entry', 'proper_closure']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('door_readings');
    }
};
