<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PersonalTrainer
 * 
 * Merepresentasikan staf pelatih (Personal Trainer/PT) yang terdaftar pada suatu cabang.
 *
 * @package App\Models
 */
class PersonalTrainer extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['code', 'name', 'status', 'branch_id'];
    
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi: PT ditugaskan pada satu Cabang.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relasi: Memiliki banyak jadwal sesi tugas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedules()
    {
        return $this->hasMany(PtSchedule::class);
    }

    /**
     * Bootstrap kejadian model.
     * Menambahkan Global Scope dan Auto-generator untuk Kode PT.
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
        
        static::creating(function ($pt) {
            if (empty($pt->code)) {
                $latest = static::where('branch_id', $pt->branch_id)->orderBy('id', 'desc')->first();
                $nextNum = 1;
                
                // Mendapatkan angka incremental dari kode PT terakhir
                if ($latest && preg_match('/-PT(\d+)$/', $latest->code, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                }
                
                $branchCode = $pt->branch ? $pt->branch->code : '000';
                $pt->code = $branchCode . '-PT' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
