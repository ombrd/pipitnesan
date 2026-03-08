<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtSchedule extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'personal_trainer_id',
        'date',
        'time_start',
        'time_end',
        'quota'
    ];
    
    protected $casts = [
        'date' => 'date',
        'time_start' => 'datetime:H:i',
        'time_end' => 'datetime:H:i',
    ];

    public function trainer()
    {
        return $this->belongsTo(PersonalTrainer::class, 'personal_trainer_id');
    }

    public function bookings()
    {
        return $this->hasMany(PtBooking::class);
    }

    protected static function booted()
    {
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->whereHas('trainer', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id);
                });
            });
        }
    }
}
