<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Branch
 * 
 * Merepresentasikan data Cabang dari fasilitas/gym.
 * Berperan sentral untuk memisahkan data (multi-tenancy ringan) sehingga setiap cabang memiliki data member dan transaksi tersendiri.
 *
 * @package App\Models
 */
class Branch extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['code', 'name', 'address', 'phone', 'manager_name'];
    
    /**
     * Relasi: Sebuah Cabang memiliki banyak Account Officer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountOfficers()
    {
        return $this->hasMany(AccountOfficer::class);
    }

    /**
     * Bootstrap kejadian (events) pada model.
     * Menerapkan filter global scope dan melakukan auto-generate kombinasi kode cabang yang baru.
     *
     * @return void
     */
    protected static function booted()
    {
        // Hanya memunculkan cabang di mana user/staff saat ini berada (jika difilter).
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->where('branches.id', auth()->user()->branch_id);
            });
        }
        
        // Auto-generate kode berformat integer dari branch terakhir, minimal 100.
        static::creating(function ($branch) {
            if (empty($branch->code)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? max(100, intval($latest->code) + 1) : 100;
                $branch->code = (string) $nextId;
            }
        });
    }
}
