<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\AccountOfficer;
use App\Models\PersonalTrainer;
use App\Models\Promotion;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PtSchedule;
use App\Models\PtBooking;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Users
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Super Admin', 'password' => 'password']
        );
        $admin->assignRole('super_admin');

        // 2. Branch
        $branch1 = Branch::firstOrCreate(
            ['name' => 'Pipitnesan Pusat'],
            ['address' => 'Jl. Jendral Sudirman No. 1, Jakarta']
        );
        $branch2 = Branch::firstOrCreate(
            ['name' => 'Pipitnesan Selatan'],
            ['address' => 'Jl. Kemang Raya No. 15, Jakarta']
        );
        
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@gym.com'],
            ['name' => 'Kasir Fitnes', 'password' => 'password', 'branch_id' => $branch1->id]
        );
        $kasir->assignRole('kasir');

        $manager = User::firstOrCreate(
            ['email' => 'manager@gym.com'],
            ['name' => 'Manager Gym', 'password' => 'password', 'branch_id' => $branch1->id]
        );
        $manager->assignRole('manager');

        // 3. Account Officer
        $ao1 = AccountOfficer::firstOrCreate(
            ['name' => 'Budi Santoso'],
            ['branch_id' => $branch1->id, 'phone' => '08123456789', 'active_date' => Carbon::now()]
        );

        // 4. Personal Trainer
        $pt1 = PersonalTrainer::firstOrCreate(
            ['name' => 'Ade Rai'],
            ['status' => true, 'branch_id' => $branch1->id]
        );
        $pt2 = PersonalTrainer::firstOrCreate(
            ['name' => 'Agung Rai'],
            ['status' => true, 'branch_id' => $branch2->id]
        );
        $pt3 = PersonalTrainer::firstOrCreate(
            ['name' => 'Bima Satria'],
            ['status' => false, 'branch_id' => $branch1->id]
        );

        // 5. Promotion
        $promo1 = Promotion::firstOrCreate(
            ['name' => 'Promo Mahasiswa 1 Bulan'],
            ['price' => 150000, 'duration_days' => 1, 'branch_id' => $branch1->id]
        );
        $promo2 = Promotion::firstOrCreate(
            ['name' => 'Paket Hemat 3 Bulan'],
            ['price' => 400000, 'duration_days' => 3, 'branch_id' => $branch2->id]
        );

        // 6. Member
        $member1 = Member::firstOrCreate(
            ['email' => 'johndoe@example.com'],
            [
                'id_card_number' => '3171234567890001',
                'name' => 'John Doe',
                'phone' => '081234567890',
                'password' => Hash::make('password'),
                'active_until' => Carbon::now()->addMonths(1),
                'branch_id' => $branch1->id,
                'account_officer_code' => $ao1->code,
                'promotion_id' => $promo1->id,
                'total_payment' => $promo1->price,
                'status' => 'active'
            ]
        );

        $member2 = Member::firstOrCreate(
            ['email' => 'janedoe@example.com'],
            [
                'id_card_number' => '3171234567890002',
                'name' => 'Jane Doe',
                'phone' => '081298765432',
                'password' => Hash::make('password'),
                'active_until' => Carbon::now()->subMonths(1), // Expired
                'branch_id' => $branch2->id,
                'status' => 'expired'
            ]
        );

        // 7. Payment
        Payment::firstOrCreate(
            ['member_id' => $member1->id, 'amount' => $promo1->price],
            ['payment_date' => Carbon::today(), 'handled_by' => $kasir->id]
        );
        Payment::firstOrCreate(
            ['member_id' => $member2->id, 'amount' => 150000],
            ['payment_date' => Carbon::today()->subDays(60), 'handled_by' => $kasir->id]
        );

        // 8. PT Schedule
        $schedule1 = PtSchedule::firstOrCreate(
            ['personal_trainer_id' => $pt1->id, 'date' => Carbon::today(), 'time_start' => '08:00:00'],
            ['time_end' => '10:00:00', 'quota' => 2]
        );
        
        $schedule2 = PtSchedule::firstOrCreate(
            ['personal_trainer_id' => $pt1->id, 'date' => Carbon::today(), 'time_start' => '10:30:00'],
            ['time_end' => '12:30:00', 'quota' => 2]
        );
        
        $schedule3 = PtSchedule::firstOrCreate(
            ['personal_trainer_id' => $pt2->id, 'date' => Carbon::tomorrow(), 'time_start' => '15:00:00'],
            ['time_end' => '17:00:00', 'quota' => 5]
        );

        // 9. PT Booking
        PtBooking::firstOrCreate(
            ['member_id' => $member1->id, 'pt_schedule_id' => $schedule1->id],
            ['status' => 'booked']
        );

        // 10. Activity Log
        ActivityLog::firstOrCreate(
            ['user_id' => $member1->id, 'action' => 'gym_attendance', 'description' => 'Member checked in successfully via QR Scanner at front desk on ' . Carbon::today()->toDateString()]
        );
    }
}
