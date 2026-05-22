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
        Schema::create('lo_ra_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
            $table->string('node_id', 50); // LoRa node identifier (e.g., LORA_001)
            $table->string('gateway_id', 50)->nullable(); // LoRa gateway identifier
            $table->enum('direction', ['inbound', 'outbound']); // Message direction
            $table->enum('message_type', ['sensor_data', 'command', 'heartbeat', 'ack', 'config']); // Message type
            $table->text('payload'); // Raw LoRa message payload
            $table->json('parsed_data')->nullable(); // Parsed sensor data
            $table->float('rssi', 8, 2)->nullable(); // Received Signal Strength Indicator
            $table->float('snr', 8, 2)->nullable(); // Signal-to-Noise Ratio
            $table->integer('spreading_factor')->nullable(); // LoRa spreading factor (7-12)
            $table->float('frequency', 10, 6)->nullable(); // Frequency in MHz
            $table->integer('bandwidth')->nullable(); // Bandwidth in Hz
            $table->boolean('is_processed')->default(false); // Message processing status
            $table->boolean('is_acknowledged')->default(false); // ACK status for outbound messages
            $table->string('status')->default('received'); // received, processed, failed, acknowledged
            $table->text('error_message')->nullable(); // Error details if processing failed
            $table->json('metadata')->nullable(); // Additional LoRa parameters
            $table->timestamp('transmitted_at')->nullable(); // When message was sent (for outbound)
            $table->timestamp('received_at')->nullable(); // When message was received (for inbound)
            $table->timestamps();
            
            $table->index(['node_id', 'created_at']);
            $table->index(['direction', 'message_type']);
            $table->index(['is_processed', 'status']);
            $table->index(['gateway_id', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lo_ra_messages');
    }
};
