<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Gunakan DB_ENCRYPTION_KEY yang statis (tidak berubah saat setup) untuk enkripsi field sensitif di database.
        // Ini memastikan field phone, id_card_number, dll. bisa selalu didekripsi meskipun APP_KEY diregenerasi.
        $dbKey = config('app.db_encryption_key');
        if ($dbKey) {
            if (str_starts_with($dbKey, 'base64:')) {
                $dbKey = base64_decode(substr($dbKey, 7));
            }
            Model::encryptUsing(new Encrypter($dbKey, config('app.cipher')));
        }
    }
}
