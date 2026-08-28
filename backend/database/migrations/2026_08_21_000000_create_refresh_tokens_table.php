<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deskripsi singkat:
 * Migration untuk membuat tabel `refresh_tokens` yang menyimpan Refresh Token opaque
 * yang digunakan untuk memperbarui Access Token (JWT) tanpa meminta user login ulang.
 *
 * Setiap baris mewakili satu Refresh Token aktif. Token di-hash dengan SHA-256 sebelum disimpan
 * agar tidak dapat disalahgunakan jika database bocor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            // Token disimpan dalam bentuk SHA-256 hash (bukan plaintext)
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['token', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
