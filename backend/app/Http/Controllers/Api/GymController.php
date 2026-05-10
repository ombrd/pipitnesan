<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\TrainingPlan;
use App\Models\Exercise;
use App\Models\UserTrainingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GymController extends Controller
{
    public function getGoals()
    {
        return response()->json(Goal::all());
    }

    public function getTrainingPlans()
    {
        return response()->json(TrainingPlan::with(['goals', 'exercises'])->get());
    }

    public function getExercises()
    {
        return response()->json(Exercise::all());
    }

    public function getRecommendations(Request $request)
    {
        $request->validate([
            'goal_ids' => 'required|array',
            'goal_ids.*' => 'exists:goals,id'
        ]);

        $goalIds = $request->goal_ids;

        // Find plans that have at least one of the selected goals
        $plans = TrainingPlan::whereHas('goals', function ($query) use ($goalIds) {
            $query->whereIn('goals.id', $goalIds);
        })->with(['goals', 'exercises'])->get();

        return response()->json($plans);
    }

    public function selectPlan(Request $request)
    {
        $request->validate([
            'training_plan_id' => 'required|exists:training_plans,id'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Deactivate previous plans if any
        UserTrainingPlan::where('user_id', $userId)
            ->where('status', 'active')
            ->update(['status' => 'dropped']);

        $userPlan = UserTrainingPlan::create([
            'user_id' => $userId,
            'training_plan_id' => $request->training_plan_id,
            'status' => 'active'
        ]);

        return response()->json($userPlan->load('trainingPlan'));
    }
}
