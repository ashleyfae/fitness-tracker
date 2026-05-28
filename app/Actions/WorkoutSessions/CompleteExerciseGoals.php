<?php

namespace App\Actions\WorkoutSessions;

use App\Models\ExerciseGoal;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSession;

class CompleteExerciseGoals
{
    public function execute(WorkoutSession $session): void
    {
        $session->loadMissing('exercises.sets', 'exercises.exercise.goals');

        foreach ($session->exercises as $workoutExercise) {
            $this->completeGoalsForWorkoutExercise($session, $workoutExercise);
        }
    }

    protected function completeGoalsForWorkoutExercise(WorkoutSession $session, WorkoutExercise $workoutExercise): void
    {
        /** @var ExerciseGoal|null $goal */
        $goal = $workoutExercise->exercise->goals()
            ->whereNull('completed_at')
            ->orderBy('sort')
            ->first();

        if (! $goal) {
            return;
        }

        $qualifyingSets = $workoutExercise->sets->filter(
            fn ($set) => $set->weight_kg >= $goal->target_weight_kg
                && $set->number_reps >= $goal->target_reps
        )->count();

        if ($qualifyingSets >= $goal->target_sets) {
            $goal->update([
                'completed_at'                    => now(),
                'completed_in_workout_session_id' => $session->id,
            ]);
        }
    }
}
