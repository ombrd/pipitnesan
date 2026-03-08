<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'member_id',
        'amount',
        'payment_date',
        'handled_by'
    ];
    
    protected $casts = [
        'payment_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    
    public function cashier()
    {
        return $this->belongsTo(User::class, 'handled_by');
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
