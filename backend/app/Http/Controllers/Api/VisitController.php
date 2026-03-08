<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Get current member's visit/attendance history
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
