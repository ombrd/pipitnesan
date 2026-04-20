<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PtSchedule
 * 
 * Merepresentasikan sesi jadwal yang dibuka oleh seorang Personal Trainer, beserta sisa kuota yang tersedia.
 *
 * @package App\Models
 */
class PtSchedule extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'personal_trainer_id',
        'date',
        'time_start',
        'time_end',
        'quota'
    ];
    
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'time_start' => 'datetime:H:i',
        'time_end' => 'datetime:H:i',
    ];

    /**
     * Relasi: Mengarah ke staf Personal Trainer yang memiliki jadwal ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trainer()
    {
        return $this->belongsTo(PersonalTrainer::class, 'personal_trainer_id');
    }

    /**
     * Relasi: Semua booking yang dilakukan member pada jadwal PT ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(PtBooking::class);
    }

    /**
     * Bootstrap kejadian model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Membatasi filter list schedule agar staf cabang hanya melihat PT dari cabangnya saja
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->whereHas('trainer', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id);
                });
            });
        }
    }
}
