<?php

namespace App\Actions\WorkoutSessions;

use App\DataTransferObjects\WorkoutExerciseData;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSession;
use App\Models\WorkoutSet;
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

        // Get previous workout sets for all exercises in one query
        $previousSetsMap = $this->getPreviousWorkoutSets($session);

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
                previousSets: $previousSetsMap->get($routineExercise->id),
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
                    previousSets: $previousSetsMap->get($workoutExercise->exercise_id),
                ));
            }
        }

        return $exercises->sortBy('sort')->values();
    }

    /**
     * Get previous workout sets for all exercises.
     * Returns a map of exercise_id => Collection<WorkoutSet>
     */
    private function getPreviousWorkoutSets(WorkoutSession $session): Collection
    {
        // Get all exercise IDs we need to look up
        $exerciseIds = $session->routine->exercises->pluck('id')
            ->merge($session->exercises->pluck('exercise_id'))
            ->unique();

        if ($exerciseIds->isEmpty()) {
            return collect();
        }

        // For each exercise, find the most recent workout session where it was performed
        $result = collect();

        foreach ($exerciseIds as $exerciseId) {
            // Find the most recent completed workout with this exercise
            $previousSets = WorkoutSet::whereHas('workoutExercise', function ($query) use ($session, $exerciseId) {
                $query->whereHas('workoutSession', function ($q) use ($session) {
                    $q->where('user_id', $session->user_id)
                        ->where('id', '!=', $session->id)
                        ->whereNotNull('ended_at');
                })
                    ->where('exercise_id', $exerciseId);
            })
                ->with('workoutExercise.workoutSession')
                ->get()
                ->sortByDesc('workoutExercise.workoutSession.ended_at')
                ->take(10) // Take sets from most recent session with this exercise
                ->sortBy('completed_at')
                ->values();

            if ($previousSets->isNotEmpty()) {
                // Only include sets from the most recent session for this exercise
                $mostRecentSessionId = $previousSets->first()->workoutExercise->workout_session_id;
                $setsFromMostRecentSession = $previousSets->filter(function ($set) use ($mostRecentSessionId) {
                    return $set->workoutExercise->workout_session_id === $mostRecentSessionId;
                })->values();

                $result->put($exerciseId, $setsFromMostRecentSession);
            }
        }

        return $result;
    }
}
