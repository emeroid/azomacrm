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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('whatsapp_device_id')->constrained();
            $table->text('message')->nullable();
            $table->integer('delay')->default(0);
            $table->string('total_recipients');
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamp('queued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'user_id', 
                'whatsapp_device_id', 
                'message', 
                'delay', 
                'total_recipients',
                'sent_count',
                'failed_count',
                'queued_at'
            ]);
        });
    }
};
