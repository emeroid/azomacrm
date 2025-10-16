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
        Schema::create('embeddable_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('marketer_id')->constrained('users');
            $table->string('primary_color');
            $table->boolean('show_product_images')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embeddable_forms');
    }
};
