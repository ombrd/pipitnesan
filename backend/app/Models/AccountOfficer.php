<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountOfficer extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['branch_id', 'code', 'name', 'phone', 'active_date'];
    
    protected $casts = [
        'active_date' => 'date',
        'phone' => 'encrypted',
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
        
        static::creating(function ($ao) {
            if (empty($ao->code)) {
                $latest = static::where('branch_id', $ao->branch_id)->orderBy('id', 'desc')->first();
                $nextNum = 1;
                if ($latest && preg_match('/-OF(\d+)$/', $latest->code, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                }
                $branchCode = $ao->branch ? $ao->branch->code : '000';
                $ao->code = $branchCode . '-OF' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
