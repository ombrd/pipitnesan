<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function getHistory()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $history = UserActivity::where('user_id', $userId)
            ->with(['trainingPlan', 'exercise'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($history);
    }

    public function logActivity(Request $request)
    {
        $request->validate([
            'training_plan_id' => 'nullable|exists:training_plans,id',
            'exercise_id' => 'nullable|exists:exercises,id',
            'notes' => 'nullable|string',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $activity = UserActivity::create([
            'user_id' => $userId,
            'training_plan_id' => $request->training_plan_id,
            'exercise_id' => $request->exercise_id,
            'notes' => $request->notes,
        ]);

        return response()->json($activity, 201);
    }
}
