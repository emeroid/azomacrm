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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('marketer_id')->nullable()->constrained('users')->nullOnDelete(); // Changed to nullOnDelete
            $table->text('notes')->nullable();
            $table->string('status')->default('processing');
            $table->foreignId('delivery_agent_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->foreignId('call_agent_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
