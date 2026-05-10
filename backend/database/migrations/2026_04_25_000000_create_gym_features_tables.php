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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Body Goals', 'Strength Goals', 'Flexibility Goals']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Body Plan', 'Strength Plan', 'Flexibility Plan']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Body Exercise', 'Strength Exercise', 'Flexibility Exercise']);
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('goal_training_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_plan_id')->constrained()->onDelete('cascade');
        });

        Schema::create('training_plan_exercise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
        });

        Schema::create('user_training_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_plan_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamps();
        });

        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('exercise_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('user_training_plans');
        Schema::dropIfExists('training_plan_exercise');
        Schema::dropIfExists('goal_training_plan');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('training_plans');
        Schema::dropIfExists('goals');
    }
};
