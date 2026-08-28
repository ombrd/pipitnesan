<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Deskripsi singkat:
 * Model Eloquent untuk tabel `refresh_tokens`.
 * Merepresentasikan satu Refresh Token opaque yang terkait dengan seorang Member.
 *
 * Setiap token disimpan sebagai SHA-256 hash. Token plaintext hanya dikirim ke client
 * saat pembuatan (login) dan tidak pernah disimpan dalam bentuk aslinya.
 *
 * @property int         $id
 * @property int         $member_id
 * @property string      $token       SHA-256 hash dari token plaintext
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon|null $revoked_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RefreshToken extends Model
{
    protected $fillable = [
        'member_id',
        'token',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'revoked_at'  => 'datetime',
    ];

    /**
     * Relasi ke Member pemilik token ini.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Deskripsi singkat:
     * Mengecek apakah token ini masih valid (belum di-revoke dan belum kedaluwarsa).
     *
     * Return value:
     * @return bool True jika token valid, false jika sudah tidak valid.
     */
    public function isValid(): bool
    {
        return is_null($this->revoked_at) && $this->expires_at->isFuture();
    }

    /**
     * Deskripsi singkat:
     * Membuat Refresh Token baru untuk member yang diberikan.
     * Menghasilkan token plaintext (dikirim ke client) dan menyimpan SHA-256 hash-nya ke database.
     *
     * Parameter:
     * @param  int $memberId  ID member yang akan memiliki token ini.
     * @param  int $ttlMinutes Durasi token dalam menit (default: 14 hari = 20160 menit).
     *
     * Return value:
     * @return array{token: string, model: RefreshToken} Array berisi plaintext token dan model yang tersimpan.
     */
    public static function createForMember(int $memberId, int $ttlMinutes = 20160): array
    {
        $plaintext = bin2hex(random_bytes(32)); // 64 karakter hex
        $hashed    = hash('sha256', $plaintext);

        $model = self::create([
            'member_id'  => $memberId,
            'token'      => $hashed,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        return ['token' => $plaintext, 'model' => $model];
    }

    /**
     * Deskripsi singkat:
     * Mencari dan memvalidasi Refresh Token berdasarkan plaintext yang diterima dari client.
     *
     * Parameter:
     * @param  string $plaintext Token plaintext yang dikirim oleh client.
     *
     * Return value:
     * @return RefreshToken|null Mengembalikan model jika valid, atau null jika tidak ditemukan/tidak valid.
     */
    public static function findValid(string $plaintext): ?self
    {
        $hashed = hash('sha256', $plaintext);

        return self::where('token', $hashed)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
