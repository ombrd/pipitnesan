<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\PtBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
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
        $projectId = config('services.fcm.project_id');
        $clientEmail = config('services.fcm.client_email');
        $privateKey = config('services.fcm.private_key');

        // Mode development: tanpa kredensial FCM, notifikasi hanya dicatat ke log.
        if (!$projectId || !$clientEmail || !$privateKey) {
            Log::info("FCM not configured. Skipped notification to [{$token}] : [{$title}] - {$body}");
            return;
        }

        try {
            $accessToken = $this->getAccessToken($clientEmail, $privateKey);

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error("FCM send failed for token [{$token}]: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('FCM send error: ' . $e->getMessage());
        }
    }

    /**
     * Deskripsi singkat:
     * Membuat OAuth2 access token Google memakai kredensial Service Account Firebase.
     * Token dibuat dengan menandatangani JWT assertion (RS256) menggunakan ekstensi OpenSSL,
     * sehingga tidak memerlukan library tambahan.
     *
     * Parameter:
     * @param  string  $clientEmail  Email Service Account Firebase (client_email).
     * @param  string  $privateKey   Private key Service Account (mendukung newline yang ter-escape sebagai \n).
     *
     * Return value:
     * @return string Access token OAuth2 yang siap dipakai untuk memanggil FCM HTTP v1 API.
     *
     * Exception:
     * @throws \RuntimeException Jika pertukaran assertion dengan access token gagal.
     */
    private function getAccessToken(string $clientEmail, string $privateKey): string
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $key = str_replace('\\n', "\n", $privateKey);
        openssl_sign("{$header}.{$payload}", $signature, $key, OPENSSL_ALGO_SHA256);

        $assertion = "{$header}.{$payload}." . $this->base64UrlEncode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]);

        if ($response->failed() || !$response->json('access_token')) {
            throw new \RuntimeException('Failed to obtain FCM access token: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Deskripsi singkat:
     * Mengenkode string ke format Base64URL (aman digunakan di dalam JWT).
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
