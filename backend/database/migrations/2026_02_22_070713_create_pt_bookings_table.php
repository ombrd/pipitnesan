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
        Schema::create('pt_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('pt_schedule_id')->constrained('pt_schedules')->cascadeOnDelete();
            $table->enum('status', ['booked', 'cancelled', 'done'])->default('booked');
            $table->timestamps();
            
            // Prevent duplicate booking for same member and schedule
            $table->unique(['member_id', 'pt_schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_bookings');
    }
};
