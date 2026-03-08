<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'price',
        'duration_days',
        'status'
    ];
    
    protected $casts = [
        'status' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function booted()
    {
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->where('branch_id', auth()->user()->branch_id);
            });
        }
        
        static::creating(function ($promo) {
            if (empty($promo->code)) {
                $latest = static::where('branch_id', $promo->branch_id)->orderBy('id', 'desc')->first();
                $nextNum = 1;
                if ($latest && preg_match('/-P(\d+)$/', $latest->code, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                }
                $branchCode = $promo->branch ? $promo->branch->code : '000';
                $promo->code = $branchCode . '-P' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
