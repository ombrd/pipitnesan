<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Member;
use App\Models\PersonalTrainer;
use App\Models\PtSchedule;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_login_with_valid_credentials()
    {
        $member = Member::create([
            'member_number' => 'MBR12345678901',
            'name' => 'John Doe',
            'password' => Hash::make('password123'),
            'active_until' => Carbon::today()->addDays(30),
        ]);

        $response = $this->postJson('/api/login', [
            'member_number' => 'MBR12345678901',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['member', 'token']);
    }

    public function test_member_cannot_login_with_invalid_credentials()
    {
        $member = Member::create([
            'member_number' => 'MBR12345678901',
            'name' => 'John Doe',
            'password' => Hash::make('password123'),
            'active_until' => Carbon::today()->addDays(30),
        ]);

        $response = $this->postJson('/api/login', [
            'member_number' => 'MBR12345678901',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_member_cannot_login_if_expired()
    {
        $member = Member::create([
            'member_number' => 'MBR12345678901',
            'name' => 'John Doe',
            'password' => Hash::make('password123'),
            'active_until' => Carbon::yesterday(),
        ]);

        $response = $this->postJson('/api/login', [
            'member_number' => 'MBR12345678901',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['member_number']);
    }

    public function test_authenticated_member_can_generate_qr()
    {
        $member = Member::create([
            'member_number' => 'MBR123',
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);

        Sanctum::actingAs($member, ['*']);

        $response = $this->getJson('/api/qr/generate');

        $response->assertStatus(200)
                 ->assertJsonStructure(['qr_token', 'expires_in']);
    }

    public function test_system_can_scan_qr_to_record_attendance()
    {
        $member = Member::create([
            'member_number' => 'MBR123',
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);

        $token = (string) Str::uuid();
        Cache::put('qr_attendance_' . $token, $member->id, now()->addMinutes(3));

        $response = $this->postJson('/api/qr/scan', [
            'qr_token' => $token,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Attendance recorded successfully']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $member->id,
            'action' => 'gym_attendance'
        ]);
        
        // Test scanning again fails
        $response2 = $this->postJson('/api/qr/scan', [
            'qr_token' => $token,
        ]);
        $response2->assertStatus(400);
    }

    public function test_can_list_active_pts()
    {
        $member = Member::create([
            'member_number' => 'MBR123',
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);

        PersonalTrainer::create(['name' => 'Active PT', 'status' => true]);
        PersonalTrainer::create(['name' => 'Inactive PT', 'status' => false]);

        Sanctum::actingAs($member, ['*']);

        $response = $this->getJson('/api/pts');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('Active PT', $response->json()[0]['name']);
    }

    public function test_can_list_pt_schedules()
    {
        $member = Member::create([
            'member_number' => 'MBR123',
            'name' => 'John Doe',
            'password' => Hash::make('password'),
            'active_until' => Carbon::now()->addYear()
        ]);

        $trainer = PersonalTrainer::create(['name' => 'Active PT', 'status' => true]);
        
        PtSchedule::create([
            'personal_trainer_id' => $trainer->id,
            'date' => Carbon::tomorrow(),
            'time_start' => '10:00:00',
            'time_end' => '11:00:00',
            'quota' => 2
        ]);
        
        // Past schedule should not be included
        PtSchedule::create([
            'personal_trainer_id' => $trainer->id,
            'date' => Carbon::yesterday(),
            'time_start' => '10:00:00',
            'time_end' => '11:00:00',
            'quota' => 2
        ]);

        Sanctum::actingAs($member, ['*']);

        $response = $this->getJson('/api/pts/' . $trainer->id . '/schedules');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }
}
