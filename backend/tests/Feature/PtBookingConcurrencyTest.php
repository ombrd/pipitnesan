<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Member;
use App\Models\PersonalTrainer;
use App\Models\PtSchedule;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class PtBookingConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_respects_quota()
    {
        $trainer = PersonalTrainer::create(['name' => 'PT 1', 'status' => true]);
        $schedule = PtSchedule::create([
            'personal_trainer_id' => $trainer->id,
            'date' => Carbon::tomorrow(),
            'time_start' => '10:00:00',
            'time_end' => '11:00:00',
            'quota' => 1
        ]);

        $member1 = Member::create([
            'member_number' => '12345678901234',
            'name' => 'Member 1',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);
        
        $member2 = Member::create([
            'member_number' => '12345678901235',
            'name' => 'Member 2',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);

        // Member 1 books successfully
        Sanctum::actingAs($member1, ['*']);
        $response1 = $this->postJson('/api/pts/book', ['pt_schedule_id' => $schedule->id]);
        
        $response1->assertStatus(200);

        // Member 2 fails due to quota
        Sanctum::actingAs($member2, ['*']);
        $response2 = $this->postJson('/api/pts/book', ['pt_schedule_id' => $schedule->id]);
        
        $response2->assertStatus(400)
                  ->assertJson(['message' => 'Schedule is full']);
    }

    public function test_booking_prevents_overlap()
    {
        $trainer = PersonalTrainer::create(['name' => 'PT 1', 'status' => true]);
        
        $schedule1 = PtSchedule::create([
            'personal_trainer_id' => $trainer->id,
            'date' => Carbon::tomorrow(),
            'time_start' => '10:00:00',
            'time_end' => '11:00:00',
            'quota' => 5
        ]);

        $schedule2 = PtSchedule::create([
            'personal_trainer_id' => $trainer->id,
            'date' => Carbon::tomorrow(),
            'time_start' => '10:30:00',
            'time_end' => '11:30:00',
            'quota' => 5
        ]);

        $member = Member::create([
            'member_number' => 'MBR123',
            'name' => 'Member 1',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);

        Sanctum::actingAs($member, ['*']);

        $response1 = $this->postJson('/api/pts/book', ['pt_schedule_id' => $schedule1->id]);
        $response1->assertStatus(200);

        $response2 = $this->postJson('/api/pts/book', ['pt_schedule_id' => $schedule2->id]);
        
        $response2->assertStatus(400)
                  ->assertJson(['message' => 'You have an overlapping booking at this time']);
    }
}
