<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountOfficer
 * 
 * Merepresentasikan data Account Officer (petugas sales/marketing) yang ditugaskan pada setiap cabang.
 * Model ini menggunakan trait Auditable untuk mencatat riwayat perubahan data.
 *
 * @package App\Models
 */
class AccountOfficer extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = ['branch_id', 'code', 'name', 'phone', 'active_date'];
    
    /**
     * Penyesuaian tipe data atribut (casting).
     * Field 'phone' dienkripsi secara otomatis pada database.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active_date' => 'date',
        'phone' => 'encrypted',
    ];
    
    /**
     * Relasi: AccountOfficer milik satu Branch (Cabang).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Bootstrap kejadian (events) pada model.
     * Menerapkan pembatasan global scope berdasarkan cabang user login dan menangani pembuatan kode otomatis.
     *
     * @return void
     */
    protected static function booted()
    {
        // Membatasi data (Global Scope) agar Staff hanya melihat AO di cabangnya sendiri.
        if (auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->branch_id) {
            static::addGlobalScope('branch', function (\Illuminate\Database\Eloquent\Builder $builder) {
                $builder->where('branch_id', auth()->user()->branch_id);
            });
        }
        
        // Auto-generate kode AO ketika record baru dibuat.
        static::creating(function ($ao) {
            if (empty($ao->code)) {
                $latest = static::where('branch_id', $ao->branch_id)->orderBy('id', 'desc')->first();
                $nextNum = 1;
                
                // Ekstraksi angka berurutan dari kode terakhir (misal: 001-OF0001 -> 1)
                if ($latest && preg_match('/-OF(\d+)$/', $latest->code, $matches)) {
                    $nextNum = intval($matches[1]) + 1;
                }
                
                $branchCode = $ao->branch ? $ao->branch->code : '000';
                $ao->code = $branchCode . '-OF' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
