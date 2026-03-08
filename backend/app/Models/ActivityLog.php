<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'user_id');
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
