<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * Class Member
 * 
 * Merepresentasikan pelanggan atau pengguna aplikasi klien (mobile).
 * Class ini menangani otentikasi JWT secara terpisah dari class User (yang digunakan oleh Admin/Staff).
 *
 * @package App\Models
 */
class Member extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable, JWTSubject, MustVerifyEmail
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, Notifiable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'member_number',
        'id_card_number',
        'name',
        'phone',
        'email',
        'password',
        'active_until',
        'address',
        'birth_place',
        'birth_date',
        'account_officer_code',
        'personal_trainer_code',
        'branch_id',
        'promotion_id',
        'total_payment',
        'status',
        'fcm_token'
    ];
    
    /**
     * Atribut yang tidak akan dikembalikan pada response JSON (disembunyikan saat serialisasi).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
    
    /**
     * Penyesuaian tipe data dan enkripsi field sensitif di level database.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active_until' => 'date',
        'phone' => 'encrypted',
        'id_card_number' => 'encrypted',
    ];

    /**
     * Mendapatkan nilai identifier yang akan disimpan di dalam token JWT (biasanya Primary Key).
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Mengembalikan klaim (claims) nilai tambahan yang ingin ditambahkan di dalam JWT Payload.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relasi: Riwayat pembayaran yang dilakukan oleh member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relasi: Booking jadwal Personal Trainer yang dilakukan member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(PtBooking::class);
    }

    /**
     * Relasi: Cabang di mana member ini didaftarkan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relasi: Promo atau paket membership yang digunakan oleh member ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Bootstrap kejadian pada model.
     *
     * @return void
     */
    protected static function booted()
    {
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->where('branch_id', auth()->user()->branch_id);
            });
        }

        static::creating(function ($member) {
            if (empty($member->member_number)) {
                $latest = static::where('branch_id', $member->branch_id)->orderBy('id', 'desc')->first();
                $nextNum = 1;
                if ($latest && preg_match('/(\d{7})$/', $member->member_number, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                }
                $branchCode = $member->branch ? $member->branch->code : '000';
                $member->member_number = $branchCode . '1111' . str_pad($nextNum, 7, '0', STR_PAD_LEFT);
            }
        });
    }
}
