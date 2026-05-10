<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index()
    {
        return response()->json(Goal::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|in:Body Goals,Strength Goals,Flexibility Goals',
            'description' => 'nullable|string',
        ]);

        $goal = Goal::create($validated);
        return response()->json($goal, 201);
    }

    public function show(Goal $goal)
    {
        return response()->json($goal);
    }

    public function update(Request $request, Goal $goal)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:Body Goals,Strength Goals,Flexibility Goals',
            'description' => 'nullable|string',
        ]);

        $goal->update($validated);
        return response()->json($goal);
    }

    public function destroy(Goal $goal)
    {
        $goal->delete();
        return response()->json(null, 204);
    }
}
