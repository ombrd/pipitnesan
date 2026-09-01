<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalTrainer;
use App\Models\PtSchedule;
use Illuminate\Http\Request;

class PTController extends Controller
{
    /**
     * Deskripsi singkat:
     * Mengambil daftar seluruh Personal Trainer (PT) yang berstatus aktif. 
     * Hasilnya dapat difilter berdasarkan cabang (branch_id) jika parameter tersebut diberikan.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Objek request klien. (Opsional) Membutuhkan 'branch_id'.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi array data Personal Trainer.
     *
     * Contoh penggunaan:
     * GET /api/pt?branch_id=1
     */
    public function index(Request $request)
    {
        $branchId = $request->query('branch_id');

        $query = PersonalTrainer::with('branch')->where('status', true);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $trainers = $query->get();

        return response()->json([
            'data' => $trainers
        ]);
    }

    /**
     * Deskripsi singkat:
     * Mengambil jadwal-jadwal (schedule) yang tersedia untuk seorang Personal Trainer (PT) spesifik.
     * Jadwal yang dikembalikan hanya jadwal yang belum berlalu (mulai dari hari ini), 
     * memiliki sisa kuota, dan belum pernah di-booking (aktif) oleh member yang sedang login.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Objek request klien. (Opsional) 'date' (YYYY-MM-DD) untuk filter tanggal.
     * @param  int  $id  Primary Key (ID) dari `PersonalTrainer`.
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi array daftar jadwal PT lengkap dengan sisa kuota.
     *
     * Contoh penggunaan:
     * GET /api/pt/5/schedules?date=2024-05-10
     * Headers: Authorization: Bearer <token>
     */
    public function schedules(Request $request, $id)
    {
        $trainer = PersonalTrainer::findOrFail($id);
        $user = auth('api')->user();

        $query = PtSchedule::where('personal_trainer_id', $trainer->id)
            ->withCount(['bookings as active_bookings_count' => function($q) {
                $q->whereIn('status', ['booked', 'done']);
            }])
            ->where('date', '>=', now()->toDateString())
            ->whereDoesntHave('bookings', function($q) use ($user) {
                $q->where('member_id', $user->id)
                  ->whereIn('status', ['booked', 'done']);
            })
            ->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc');

        if ($request->has('date')) {
            $query->whereDate('date', $request->query('date'));
        }

        $schedules = $query->get()->map(function($schedule) {
            $schedule->remaining_quota = max(0, $schedule->quota - $schedule->active_bookings_count);
            return $schedule;
        });

        return response()->json([
            'data' => $schedules
        ]);
    }
}
