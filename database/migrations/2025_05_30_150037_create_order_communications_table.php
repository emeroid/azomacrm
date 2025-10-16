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
        Schema::create('order_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users');
            $table->enum('type', ['call', 'email', 'note']);
            $table->enum('direction', ['inbound', 'outbound'])->nullable(); // Optional
            $table->text('content');
            $table->json('labels')->nullable();
            $table->json('outcome')->nullable();
            $table->boolean('is_read')->default(false); // Optional
            $table->timestamp('communication_time')->useCurrent(); // Optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_communications');
    }
};
