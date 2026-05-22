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
        Schema::create('vibration_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->float('x_axis', 8, 4); // Getaran sumbu X
            $table->float('y_axis', 8, 4); // Getaran sumbu Y  
            $table->float('z_axis', 8, 4); // Getaran sumbu Z
            $table->float('magnitude', 8, 4); // Total magnitude getaran
            $table->boolean('is_abnormal')->default(false); // Status getaran abnormal
            $table->float('threshold', 8, 4)->default(2.0); // Batas normal getaran
            $table->string('status')->default('normal'); // normal, warning, critical
            $table->json('metadata')->nullable(); // Data tambahan sensor
            $table->timestamp('recorded_at'); // Waktu pembacaan sensor
            $table->timestamps();
            
            $table->index(['device_id', 'recorded_at']);
            $table->index(['is_abnormal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vibration_readings');
    }
};
