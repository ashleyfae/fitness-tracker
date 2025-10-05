<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutSetRequest;
use App\Http\Requests\UpdateWorkoutSetRequest;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutSet;

class WorkoutSetController extends Controller
{
    /**
     * Add a new set to an existing workout exercise.
     */
    public function store(StoreWorkoutSetRequest $request, WorkoutSession $workoutSession, WorkoutExercise $workoutExercise)
    {
        $validated = $request->validated();

        $set = $workoutExercise->sets()->create([
            'weight_kg' => $validated['weight_kg'],
            'number_reps' => $validated['number_reps'],
            'completed_at' => now(),
        ]);

        // Observer automatically updates number_sets count

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'set' => $set,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }

    /**
     * Update an existing set.
     */
    public function update(UpdateWorkoutSetRequest $request, WorkoutSession $workoutSession, WorkoutExercise $workoutExercise, WorkoutSet $workoutSet)
    {
        $workoutSet->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'set' => $workoutSet,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }

    /**
     * Delete a set.
     */
    public function destroy(WorkoutSession $workoutSession, WorkoutExercise $workoutExercise, WorkoutSet $workoutSet)
    {
        $workoutSet->delete();

        // Observer automatically updates number_sets count and deletes exercise if needed

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('workouts.edit', $workoutSession);
    }
}
