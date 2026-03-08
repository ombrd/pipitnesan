<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\ActivityLog;

class QrController extends Controller
{
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
