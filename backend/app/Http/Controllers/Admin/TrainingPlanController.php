<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingPlan;
use Illuminate\Http\Request;

class TrainingPlanController extends Controller
{
    public function index()
    {
        return response()->json(TrainingPlan::with(['goals', 'exercises'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|in:Body Plan,Strength Plan,Flexibility Plan',
            'description' => 'nullable|string',
            'goals' => 'array',
            'goals.*' => 'exists:goals,id',
            'exercises' => 'array',
            'exercises.*' => 'exists:exercises,id',
        ]);

        $plan = TrainingPlan::create($validated);
        
        if (isset($validated['goals'])) {
            $plan->goals()->sync($validated['goals']);
        }
        
        if (isset($validated['exercises'])) {
            $plan->exercises()->sync($validated['exercises']);
        }

        return response()->json($plan->load(['goals', 'exercises']), 201);
    }

    public function show(TrainingPlan $trainingPlan)
    {
        return response()->json($trainingPlan->load(['goals', 'exercises']));
    }

    public function update(Request $request, TrainingPlan $trainingPlan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:Body Plan,Strength Plan,Flexibility Plan',
            'description' => 'nullable|string',
            'goals' => 'array',
            'goals.*' => 'exists:goals,id',
            'exercises' => 'array',
            'exercises.*' => 'exists:exercises,id',
        ]);

        $trainingPlan->update($validated);

        if (isset($validated['goals'])) {
            $trainingPlan->goals()->sync($validated['goals']);
        }
        
        if (isset($validated['exercises'])) {
            $trainingPlan->exercises()->sync($validated['exercises']);
        }

        return response()->json($trainingPlan->load(['goals', 'exercises']));
    }

    public function destroy(TrainingPlan $trainingPlan)
    {
        $trainingPlan->delete();
        return response()->json(null, 204);
    }
}
