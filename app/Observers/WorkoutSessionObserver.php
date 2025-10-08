<?php

namespace App\Observers;

use App\Models\WorkoutSession;
use App\Services\PersonalRecordService;

class WorkoutSessionObserver
{
    public function __construct(
        protected PersonalRecordService $prService
    ) {}

    /**
     * Handle the WorkoutSession "updated" event.
     * When ended_at is set, calculate totals and update personal records.
     */
    public function updated(WorkoutSession $workoutSession): void
    {
        // Only recalculate if ended_at was just set
        if ($workoutSession->isDirty('ended_at') && $workoutSession->ended_at !== null) {
            // Update workout totals
            $workoutSession->updateQuietly([
                'total_exercises' => $workoutSession->exercises()->count(),
                'total_kg_lifted' => $workoutSession->exercises()
                    ->with('sets')
                    ->get()
                    ->flatMap->sets
                    ->sum(fn ($set) => $set->weight_kg * $set->number_reps),
            ]);

            // Update personal records for all exercises in this workout
            $this->prService->updateRecordsForWorkoutSession($workoutSession->id);
        }
    }
}
