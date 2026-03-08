<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Member extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable, JWTSubject, MustVerifyEmail
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, Notifiable;
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
    
    protected $hidden = [
        'password',
    ];
    
    protected $casts = [
        'active_until' => 'date',
        'phone' => 'encrypted',
        'id_card_number' => 'encrypted',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function bookings()
    {
        return $this->hasMany(PtBooking::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

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
