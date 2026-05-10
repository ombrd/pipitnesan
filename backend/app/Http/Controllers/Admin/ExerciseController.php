<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index()
    {
        return response()->json(Exercise::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|in:Body Exercise,Strength Exercise,Flexibility Exercise',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $exercise = Exercise::create($validated);
        return response()->json($exercise, 201);
    }

    public function show(Exercise $exercise)
    {
        return response()->json($exercise);
    }

    public function update(Request $request, Exercise $exercise)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:Body Exercise,Strength Exercise,Flexibility Exercise',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $exercise->update($validated);
        return response()->json($exercise);
    }

    public function destroy(Exercise $exercise)
    {
        $exercise->delete();
        return response()->json(null, 204);
    }
}
