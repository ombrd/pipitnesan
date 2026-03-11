<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\ActivityLog;

class QrController extends Controller
{
    /**
     * Deskripsi singkat:
     * Menghasilkan token QR untuk keperluan absensi manual (sebagai alternatif `generateQR` pada AuthController).
     * Token ini disimpan dalam cache dengan masa berlaku selama 3 menit.
     *
     * Parameter:
     * (Tidak ada parameter spesifik, menggunakan token JWT dari header Auth)
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi string token QR acak dan masa berlaku.
     *
     * Contoh penggunaan:
     * GET /api/qr/generate
     * Headers: Authorization: Bearer <token>
     */
    public function generate(Request $request)
    {
        $member = $request->user();
        
        if ($member->active_until && $member->active_until->isPast()) {
            return response()->json(['message' => 'Membership expired'], 403);
        }

        $token = (string) Str::uuid();

        // Token valid for 3 minutes
        Cache::put('qr_attendance_' . $token, $member->id, now()->addMinutes(3));

        return response()->json([
            'qr_token' => $token,
            'expires_in' => 180,
        ]);
    }

    /**
     * Deskripsi singkat:
     * Memindai dan memvalidasi token QR absensi yang telah di-generate. 
     * Jika valid, akan mencatat kehadiran (ActivityLog action 'gym_attendance') untuk member tersebut.
     *
     * Parameter:
     * @param  \Illuminate\Http\Request  $request  Objek request klien. Membutuhkan 'qr_token' (string).
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON sukses (kode 200) atau error kedaluwarsa (kode 400).
     *
     * Contoh penggunaan:
     * POST /api/qr/scan
     * Body JSON: { "qr_token": "a1b2c3d4-e5f6-7890-..." }
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        // Consume the QR code
        $memberId = Cache::pull('qr_attendance_' . $request->qr_token);

        if (!$memberId) {
            return response()->json(['message' => 'Invalid or expired QR code'], 400);
        }

        ActivityLog::create([
            'user_id' => $memberId,
            'action' => 'gym_attendance',
            'description' => 'Member checked in successfully via QR Scanner at front desk.',
        ]);

        return response()->json(['message' => 'Attendance recorded successfully']);
    }
}
