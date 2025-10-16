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
        Schema::table('order_communications', function (Blueprint $table) {
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::table('order_communications', function (Blueprint $table) {
            $table->dropColumn(['status_before', 'status_after', 'metadata', 'sender_id']);
        });
    }
};
