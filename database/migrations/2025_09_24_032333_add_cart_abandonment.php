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
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('status')->default('draft'); // draft, submitted, abandoned
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('session_id')->nullable(); // To track the same session
            $table->index(['status', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn(['status', 'abandoned_at', 'submitted_at', 'session_id']);
        });
    }
};
