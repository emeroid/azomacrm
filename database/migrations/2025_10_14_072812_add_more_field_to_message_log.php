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
        Schema::table('message_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('scheduled_message_id')->nullable()->constrained();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->foreignId('auto_responder_log_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'scheduled_message_id', 'campaign_id', 'auto_responder_log_id']);
        });
    }
};
