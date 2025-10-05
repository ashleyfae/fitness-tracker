<?php

namespace App\Observers;

use App\Models\WorkoutSession;

class WorkoutSessionObserver
{
    /**
     * Handle the WorkoutSession "updated" event.
     * When ended_at is set, calculate total_exercises and total_kg_lifted.
     */
    public function updated(WorkoutSession $workoutSession): void
    {
        // Only recalculate if ended_at was just set
        if ($workoutSession->isDirty('ended_at') && $workoutSession->ended_at !== null) {
            $workoutSession->updateQuietly([
                'total_exercises' => $workoutSession->exercises()->count(),
                'total_kg_lifted' => $workoutSession->exercises()
                    ->with('sets')
                    ->get()
                    ->flatMap->sets
                    ->sum(fn ($set) => $set->weight_kg * $set->number_reps),
            ]);
        }
    }
}
