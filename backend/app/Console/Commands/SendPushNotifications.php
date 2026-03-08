<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\PtBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-fcm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications for expiring memberships and PT booking reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting push notification scan...');

        $this->notifyExpiringMemberships();
        $this->notifyTodayBookings();

        $this->info('Push notification scan completed.');
    }

    private function notifyExpiringMemberships()
    {
        // Find members whose membership expires in 3 days
        $targetDate = Carbon::today()->addDays(3)->toDateString();
        
        $members = Member::whereNotNull('fcm_token')
            ->whereDate('active_until', $targetDate)
            ->where('status', 'active')
            ->get();

        foreach ($members as $member) {
            $title = "Membership Expiring Soon!";
            $body = "Hi {$member->name}, your membership expires in 3 days. Please renew to continue enjoying Pipitnesan.";
            $this->sendFcm($member->fcm_token, $title, $body);
        }
    }

    private function notifyTodayBookings()
    {
        $today = Carbon::today()->toDateString();
        
        $bookings = PtBooking::with(['member', 'schedule.trainer'])
            ->whereHas('schedule', function($q) use ($today) {
                $q->whereDate('date', $today);
            })
            ->where('status', 'booked')
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->member && $booking->member->fcm_token) {
                $title = "PT Session Today!";
                $trainerName = $booking->schedule->trainer->name;
                $time = $booking->schedule->time_start;
                $body = "Reminder: You have a session with {$trainerName} today at {$time}. Don't be late!";
                $this->sendFcm($booking->member->fcm_token, $title, $body);
            }
        }
    }

    private function sendFcm($token, $title, $body)
    {
        // Mock FCM sending logic. In a real app, use Kreait\Firebase or a similar library.
        Log::info("FCM Sent to [{$token}] : [{$title}] - {$body}");
    }
}
