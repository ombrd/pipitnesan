<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\ActivityLog;
use Carbon\Carbon;

class AutoCheckout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-checkout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically checkout members who checked in today but have not checked out, based on parameterized time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $autoCheckoutTime = Setting::getValue('auto_checkout_time', '23:00');
        
        $currentTime = Carbon::now()->format('H:i');

        // Only run if the current time matches the auto_checkout_time
        if ($currentTime === $autoCheckoutTime) {
            $this->info("Running auto checkout at {$currentTime}");
            
            $today = Carbon::today();

            // Find users who have check-in logs today
            $checkins = ActivityLog::where('action', 'gym_attendance')
                ->whereDate('created_at', $today)
                ->get()
                ->groupBy('user_id');

            // Find users who have check-out logs today
            $checkouts = ActivityLog::where('action', 'gym_checkout')
                ->whereDate('created_at', $today)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $checkoutCount = 0;

            foreach ($checkins as $userId => $logs) {
                // If user doesn't have a checkout today, create one
                if (!in_array($userId, $checkouts)) {
                    ActivityLog::create([
                        'user_id' => $userId,
                        'action' => 'gym_checkout',
                        'description' => 'System automated check-out at ' . $currentTime,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    $checkoutCount++;
                }
            }

            $this->info("Auto checkout completed. {$checkoutCount} members checked out.");
        } else {
            $this->info("Current time {$currentTime} does not match auto checkout time {$autoCheckoutTime}. Skipping.");
        }
    }
}
