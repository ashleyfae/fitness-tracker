<?php

namespace App\Observers;

use App\Models\WorkoutSet;

class WorkoutSetObserver
{
    /**
     * Handle the WorkoutSet "created" event.
     * Update the parent WorkoutExercise's number_sets count.
     */
    public function created(WorkoutSet $workoutSet): void
    {
        $this->updateWorkoutExerciseSetCount($workoutSet);
    }

    /**
     * Handle the WorkoutSet "deleted" event.
     * Update the parent WorkoutExercise's number_sets count.
     * If no sets remain, delete the WorkoutExercise.
     */
    public function deleted(WorkoutSet $workoutSet): void
    {
        $workoutExercise = $workoutSet->workoutExercise;

        if (! $workoutExercise) {
            return;
        }

        $setCount = $workoutExercise->sets()->count();

        if ($setCount === 0) {
            // No sets remain, delete the workout exercise
            $workoutExercise->delete();
        } else {
            // Update the count
            $workoutExercise->updateQuietly([
                'number_sets' => $setCount,
            ]);
        }
    }

    /**
     * Update the parent WorkoutExercise's number_sets count.
     */
    private function updateWorkoutExerciseSetCount(WorkoutSet $workoutSet): void
    {
        $workoutExercise = $workoutSet->workoutExercise;

        if (! $workoutExercise) {
            return;
        }

        $workoutExercise->updateQuietly([
            'number_sets' => $workoutExercise->sets()->count(),
        ]);
    }
}
