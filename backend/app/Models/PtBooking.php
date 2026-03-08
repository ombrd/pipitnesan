<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtBooking extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'member_id',
        'pt_schedule_id',
        'status',
        'cancel_reason'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function schedule()
    {
        return $this->belongsTo(PtSchedule::class, 'pt_schedule_id');
    }

    protected static function booted()
    {
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->whereHas('member', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id);
                });
            });
        }
    }
}
