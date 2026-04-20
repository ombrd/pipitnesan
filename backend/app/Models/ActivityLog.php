<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityLog
 * 
 * Merepresentasikan catatan aktivitas yang dilakukan oleh Member (misalnya login, pendaftaran awal, dsb).
 *
 * @package App\Models
 */
class ActivityLog extends Model
{
    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description'
    ];

    /**
     * Relasi: ActivityLog dimiliki oleh satu Member.
     * Perhatikan bahwa field `user_id` di database sebenarnya mereferensikan ke tabel members.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'user_id');
    }

    /**
     * Bootstrap kejadian (events) pada model.
     * Menerapkan pembatasan global scope berdasarkan cabang user/staff yang login.
     *
     * @return void
     */
    protected static function booted()
    {
        // Staff hanya dapat melihat log aktivitas milik member di cabangnya sendiri.
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->whereHas('member', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id);
                });
            });
        }
    }
}
