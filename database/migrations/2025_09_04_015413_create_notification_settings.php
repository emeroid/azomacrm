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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // e.g., 'marketer', 'call_agent', 'customer'
            $table->string('notification_type'); // e.g., 'new_order', 'order_assigned'
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['role', 'notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
