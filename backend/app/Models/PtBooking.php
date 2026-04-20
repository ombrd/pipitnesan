<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PtBooking
 * 
 * Merepresentasikan pencatatan/booking jadwal (Schedule) Personal Trainer yang dilakukan oleh Member.
 *
 * @package App\Models
 */
class PtBooking extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'pt_schedule_id',
        'status',
        'cancel_reason'
    ];

    /**
     * Relasi: Booking dilakukan oleh seorang member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Relasi: Mengarah pada jadwal PT spesifik yang dibooking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function schedule()
    {
        return $this->belongsTo(PtSchedule::class, 'pt_schedule_id');
    }

    /**
     * Bootstrap kejadian model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Membatasi filter cabang sehingga staf tak sengaja melihat/mengatur booking PT cabang orang lain
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->whereHas('member', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id);
                });
            });
        }
    }
}
