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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null'); // Device yang memicu alert
            $table->foreignId('sensor_id')->nullable()->constrained()->onDelete('set null'); // Sensor yang memicu alert
            $table->foreignId('activity_log_id')->nullable()->constrained()->onDelete('set null'); // Relasi ke activity log
            $table->string('alert_type'); // Tipe alert (security_breach, sensor_offline, low_battery, dll)
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium'); // Prioritas alert
            $table->enum('status', ['active', 'acknowledged', 'resolved', 'dismissed'])->default('active'); // Status alert
            $table->string('title'); // Judul alert
            $table->text('message'); // Pesan alert
            $table->json('alert_data')->nullable(); // Data alert dalam JSON
            $table->string('location')->nullable(); // Lokasi alert
            $table->timestamp('triggered_at'); // Waktu alert dipicu
            $table->timestamp('acknowledged_at')->nullable(); // Waktu alert diakui
            $table->timestamp('resolved_at')->nullable(); // Waktu alert diselesaikan
            $table->string('acknowledged_by')->nullable(); // Siapa yang mengakui
            $table->string('resolved_by')->nullable(); // Siapa yang menyelesaikan
            $table->text('resolution_notes')->nullable(); // Catatan penyelesaian
            $table->boolean('is_sent_notification')->default(false); // Apakah notifikasi sudah dikirim
            $table->json('notification_channels')->nullable(); // Channel notifikasi yang digunakan
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['alert_type', 'status']);
            $table->index(['priority', 'triggered_at']);
            $table->index(['status', 'triggered_at']);
            $table->index('triggered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
