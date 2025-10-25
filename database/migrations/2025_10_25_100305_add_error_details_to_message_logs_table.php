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
            $table->string('failure_code')->nullable()->after('failure_reason');
            $table->text('friendly_error')->nullable()->after('failure_code');
            $table->string('media_url')->nullable()->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_logs', function (Blueprint $table) {
            $table->dropColumn(['failure_code', 'friendly_error', 'media_url']);
        });
    }
};
