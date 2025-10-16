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
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('form_templates');
            $table->string('name'); // e.g., "fullname"
            $table->string('label'); // e.g., "Full Name"
            $table->string('type'); // e.g., "text", "number", "select", etc.
            $table->boolean('is_required')->default(false);
            $table->json('properties')->nullable(); // JSON containing field-specific properties
            $table->integer('order')->default(0);
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
