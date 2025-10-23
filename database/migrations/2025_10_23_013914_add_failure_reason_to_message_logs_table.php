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
            $table->text('failure_reason')->nullable()->after('read_at');
            // Index for quickly finding temp IDs
            $table->index('message_id'); 
            // Index for quickly finding all messages for a campaign
            $table->index('campaign_id');
            $table->index('scheduled_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropColumn('failure_reason');
        });
    }
};
