<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['code', 'name', 'address', 'phone', 'manager_name'];
    
    public function accountOfficers()
    {
        return $this->hasMany(AccountOfficer::class);
    }

    protected static function booted()
    {
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->where('branches.id', auth()->user()->branch_id);
            });
        }
        
        static::creating(function ($branch) {
            if (empty($branch->code)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? max(100, intval($latest->code) + 1) : 100;
                $branch->code = (string) $nextId;
            }
        });
    }
}
