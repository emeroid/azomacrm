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
        Schema::table('scheduled_messages', function (Blueprint $table) {
            $table->foreignId('whatsapp_device_id')->nullable()->constrained('whatsapp_devices'); // Use device ID instead of session ID directly
            
            // New polymorphic fields for the action target
            $table->string('action_type'); 
            $table->unsignedBigInteger('action_id')->nullable(); // For a single, specific target (e.g., Order ID)
        
            // New fields for scheduling logic
            $table->json('target_criteria')->nullable(); // e.g., {'status': 'processing'} or {'form_template_id': 5}
            $table->string('whatsapp_field_name')->nullable(); // The dynamic form field name (e.g., 'mobile', 'phone_number')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_messages', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'recipient_number']);
        });
    }
};
