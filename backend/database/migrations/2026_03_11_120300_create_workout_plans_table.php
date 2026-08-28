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
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->string('goal')->comment('weight_loss, muscle_gain, endurance, maintenance');
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->decimal('min_bmi', 5, 2)->nullable();
            $table->decimal('max_bmi', 5, 2)->nullable();
            $table->json('schedule')->comment('Array of 7 days workout schedule');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};
