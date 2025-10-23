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
        Schema::table('auto_responders', function (Blueprint $table) {
            $table->enum('match_type', ['exact', 'contains', 'starts_with'])
                  ->default('exact')
                  ->after('keyword');
                  
            // What kind of message to reply to
            $table->enum('reply_condition', ['text_only', 'media_only', 'text_or_media'])
                  ->default('text_only')
                  ->after('response_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_responders', function (Blueprint $table) {
            $table->dropColumn(['match_type', 'reply_condition']);
        });
    }
};
