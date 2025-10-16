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
        Schema::create('auto_responder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_responder_id')->constrained(); // The staff member who scheduled it
            $table->string('recipient')->nullable();
            $table->timestamp('sent_at');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responder_logs');
    }
};
