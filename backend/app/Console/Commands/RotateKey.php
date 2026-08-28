<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use App\Models\Member;
use App\Models\AccountOfficer;

class RotateKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rotate-key {old_key : The old APP_KEY (including base64: prefix if applicable)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate database encrypted fields from an old APP_KEY to the current APP_KEY';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oldKeyInput = $this->argument('old_key');
        $oldKey = $oldKeyInput;

        if (str_starts_with($oldKey, 'base64:')) {
            $oldKey = base64_decode(substr($oldKey, 7));
        }

        try {
            $oldEncrypter = new Encrypter($oldKey, config('app.cipher'));
        } catch (\Exception $e) {
            $this->error("Invalid old key format: " . $e->getMessage());
            return 1;
        }

        $this->info("Starting encryption key rotation...");

        // 1. Rotate Members
        $members = Member::all();
        $memberCount = 0;
        foreach ($members as $member) {
            $rawPhone = $member->getRawOriginal('phone');
            $rawIdCard = $member->getRawOriginal('id_card_number');
            $updated = false;

            if ($rawPhone && !$this->isFailedKeyPlaceholder($rawPhone)) {
                try {
                    $decrypted = $oldEncrypter->decrypt($rawPhone);
                    $member->phone = $decrypted; // Will be encrypted with new key on save
                    $updated = true;
                } catch (\Exception $e) {
                    // Skip if decrypt fails
                }
            }

            if ($rawIdCard && !$this->isFailedKeyPlaceholder($rawIdCard)) {
                try {
                    $decrypted = $oldEncrypter->decrypt($rawIdCard);
                    $member->id_card_number = $decrypted; // Will be encrypted with new key on save
                    $updated = true;
                } catch (\Exception $e) {
                    // Skip
                }
            }

            if ($updated) {
                $member->save();
                $memberCount++;
            }
        }
        $this->info("Rotated {$memberCount} Member records.");

        // 2. Rotate Account Officers
        $aos = AccountOfficer::all();
        $aoCount = 0;
        foreach ($aos as $ao) {
            $rawPhone = $ao->getRawOriginal('phone');
            if ($rawPhone && !$this->isFailedKeyPlaceholder($rawPhone)) {
                try {
                    $decrypted = $oldEncrypter->decrypt($rawPhone);
                    $ao->phone = $decrypted; // Will be encrypted with new key on save
                    $ao->save();
                    $aoCount++;
                } catch (\Exception $e) {
                    // Skip
                }
            }
        }
        $this->info("Rotated {$aoCount} Account Officer records.");

        $this->info("Key rotation complete!");
        return 0;
    }

    /**
     * Check if the raw value is already our failure placeholder
     */
    private function isFailedKeyPlaceholder($value)
    {
        return $value === '[Error: Invalid Key]';
    }
}
