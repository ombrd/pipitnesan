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
        Schema::create('pt_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_trainer_id')->constrained('personal_trainers')->cascadeOnDelete();
            $table->date('date');
            $table->time('time_start');
            $table->time('time_end');
            $table->integer('quota')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_schedules');
    }
};
