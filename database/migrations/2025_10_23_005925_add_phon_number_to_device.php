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
        Schema::table('whatsapp_devices', function (Blueprint $table) {
            $table->string("phone_number")->nullable();
            $table->unsignedInteger("min_delay")->default(20);
            $table->unsignedInteger("max_delay")->default(60);
            $table->boolean('auto_responder_enabled')->default(false);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_devices', function (Blueprint $table) {
            $table->dropColumn(["phone_number", "min_delay", "max_delay"]);
        });
    }
};
