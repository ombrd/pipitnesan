<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify correct SMTP credentials.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Attempting to send a test email to: {$email}...");

        try {
            Mail::raw("Hello! 👋\n\nThis is a test email from your Pipitnesan App.\nIf you are reading this, your Gmail SMTP configuration in the .env file is working perfectly!\n\nBest regards,\nPipitnesan System.", function ($msg) use ($email) {
                $msg->to($email)->subject('Pipitnesan Gym - SMTP Test Successful');
            });
            
            $this->info('✅ Test email originated from Laravel successfully! Please check the inbox of ' . $email);
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email.');
            $this->error('Error Message: ' . $e->getMessage());
            $this->warn('Tip: Make sure you have entered the correct 16-digit Google App Password in the MAIL_PASSWORD field of your .env file, and restarted your application.');
        }

        return 0;
    }
}
