<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * 
 * Merepresentasikan transaksi pembayaran yang dilakukan oleh member, misalnya pembayaran biaya pendaftaran atau perpanjangan.
 *
 * @package App\Models
 */
class Payment extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'amount',
        'payment_date',
        'handled_by'
    ];
    
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
    ];

    /**
     * Relasi: Setiap pembayaran terkait dengan satu member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    
    /**
     * Relasi: Staf (kasir) yang memproses transaksi pembayaran ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Bootstrap kejadian pada model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Menyaring data transaksi pembayaran agar staf hanya dapat melihat data cabangnya sendiri.
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->whereHas('member', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id);
                });
            });
        }
    }
}
