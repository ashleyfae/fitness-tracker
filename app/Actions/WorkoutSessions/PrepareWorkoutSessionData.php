<?php

namespace App\Actions\WorkoutSessions;

use App\DataTransferObjects\WorkoutExerciseData;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSession;
use Illuminate\Support\Collection;

class PrepareWorkoutSessionData
{
    /**
     * Prepare workout session data by merging expected (from routine)
     * and actual (from workout_exercises) data.
     *
     * @return Collection<WorkoutExerciseData>
     */
    public function execute(WorkoutSession $session): Collection
    {
        $session->load([
            'routine.exercises',
            'exercises.exercise',
            'exercises.sets',
        ]);

        $exercises = collect();

        // Add exercises from routine
        foreach ($session->routine->exercises as $routineExercise) {
            /** @var WorkoutExercise|null $workoutExercise */
            $workoutExercise = $session->exercises
                ->firstWhere('exercise_id', $routineExercise->id);

            $exercises->push(new WorkoutExerciseData(
                exercise: $routineExercise,
                expectedSets: $routineExercise->pivot->number_sets,
                restSeconds: $routineExercise->pivot->rest_seconds,
                sort: $workoutExercise?->sort ?? $routineExercise->pivot->sort,
                actualSets: $workoutExercise?->sets ?? collect(),
                workoutExerciseId: $workoutExercise?->id,
                fromRoutine: true,
            ));
        }

        // Add exercises not in routine (manually added during workout)
        $routineExerciseIds = $session->routine->exercises->pluck('id');
        foreach ($session->exercises as $workoutExercise) {
            if (! $routineExerciseIds->contains($workoutExercise->exercise_id)) {
                $exercises->push(new WorkoutExerciseData(
                    exercise: $workoutExercise->exercise,
                    expectedSets: 1, // we can only expect at least 1
                    restSeconds: $workoutExercise->rest_seconds,
                    sort: $workoutExercise->sort,
                    actualSets: $workoutExercise->sets,
                    workoutExerciseId: $workoutExercise->id,
                    fromRoutine: false,
                ));
            }
        }

        return $exercises->sortBy('sort')->values();
    }
}
