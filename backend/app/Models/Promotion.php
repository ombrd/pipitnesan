<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Promotion
 * 
 * Merepresentasikan paket keanggotaan (membership) atau promosi harga yang ditawarkan pada suatu cabang.
 *
 * @package App\Models
 */
class Promotion extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'price',
        'duration_days',
        'status'
    ];
    
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi: Promosi ini hanya berlaku pada satu cabang tertentu.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Bootstrap kejadian model.
     * Menambahkan scope cabang dan me-generate kode promo unik.
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
        
        static::creating(function ($promo) {
            // Auto generates Code for a promotion if empty
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
