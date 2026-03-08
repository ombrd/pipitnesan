<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalTrainer extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['code', 'name', 'status', 'branch_id'];
    
    protected $casts = [
        'status' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function schedules()
    {
        return $this->hasMany(PtSchedule::class);
    }

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
                if ($latest && preg_match('/-PT(\d+)$/', $latest->code, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                }
                $branchCode = $pt->branch ? $pt->branch->code : '000';
                $pt->code = $branchCode . '-PT' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
