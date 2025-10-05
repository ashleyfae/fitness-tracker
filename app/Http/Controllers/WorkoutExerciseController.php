<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutExerciseRequest;
use App\Models\WorkoutSession;

class WorkoutExerciseController extends Controller
{
    /**
     * Create a workout exercise and its first set atomically.
     */
    public function store(StoreWorkoutExerciseRequest $request, WorkoutSession $workoutSession)
    {
        $validated = $request->validated();

        // Create workout exercise
        $workoutExercise = $workoutSession->exercises()->create([
            'exercise_id' => $validated['exercise_id'],
            'number_sets' => 1, // Will be updated as more sets are added
            'rest_seconds' => $validated['rest_seconds'],
            'sort' => $validated['sort'],
        ]);

        // Create first set
        $set = $workoutExercise->sets()->create([
            'weight_kg' => $validated['weight_kg'],
            'number_reps' => $validated['number_reps'],
            'completed_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'workout_exercise_id' => $workoutExercise->id,
                'set' => $set,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }
}
