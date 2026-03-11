<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Deskripsi singkat:
     * Mengambil riwayat kunjungan (absensi gym) belonging ke member yang sedang login.
     * Data diambil dari `ActivityLog` dengan aksi spesifik 'gym_attendance'.
     *
     * Parameter:
     * (Tidak ada parameter spesifik, menggunakan token JWT dari header Auth)
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi array riwayat absensi terbaru.
     *
     * Contoh penggunaan:
     * GET /api/visits
     * Headers: Authorization: Bearer <token>
     */
    public function index()
    {
        $user = auth('api')->user();

        // Attendance is recorded as 'gym_attendance' in ActivityLog where user_id is the member's ID
        $visits = ActivityLog::where('user_id', $user->id)
            ->where('action', 'gym_attendance')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $visits
        ]);
    }
}
