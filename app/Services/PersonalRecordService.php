<?php

namespace App\Services;

use App\Models\ExerciseRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonalRecordService
{
    /**
     * Calculate and update personal records for a specific exercise
     */
    public function updateRecordsForExercise(User $user, int $exerciseId): void
    {
        // Find the set with the highest estimated 1RM (Epley formula)
        $bestSet = DB::table('workout_sets')
            ->join('workout_exercises', 'workout_sets.workout_exercise_id', '=', 'workout_exercises.id')
            ->join('workout_sessions', 'workout_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('workout_sessions.user_id', $user->id)
            ->where('workout_exercises.exercise_id', $exerciseId)
            ->whereNotNull('workout_sessions.ended_at') // Only completed workouts
            ->select(
                'workout_sets.weight_kg',
                'workout_sets.number_reps',
                'workout_sets.completed_at',
                DB::raw('workout_sets.weight_kg * (1 + workout_sets.number_reps / 30.0) as estimated_1rm')
            )
            ->orderBy('estimated_1rm', 'desc')
            ->orderBy('workout_sets.completed_at', 'asc') // If tied, take earliest
            ->first();

        if (!$bestSet) {
            return; // No sets found for this exercise
        }

        // Also find the actual heaviest weight lifted (for best_weight_kg)
        $heaviestSet = DB::table('workout_sets')
            ->join('workout_exercises', 'workout_sets.workout_exercise_id', '=', 'workout_exercises.id')
            ->join('workout_sessions', 'workout_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('workout_sessions.user_id', $user->id)
            ->where('workout_exercises.exercise_id', $exerciseId)
            ->whereNotNull('workout_sessions.ended_at')
            ->orderBy('workout_sets.weight_kg', 'desc')
            ->orderBy('workout_sets.completed_at', 'asc')
            ->select('workout_sets.weight_kg')
            ->first();

        // Update or create the exercise record
        ExerciseRecord::updateOrCreate(
            [
                'user_id' => $user->id,
                'exercise_id' => $exerciseId,
            ],
            [
                'best_weight_kg' => $heaviestSet->weight_kg,
                'estimated_1rm_kg' => round($bestSet->estimated_1rm, 2),
                'achieved_at' => $bestSet->completed_at,
            ]
        );
    }

    /**
     * Calculate and update personal records for all exercises in a workout session
     */
    public function updateRecordsForWorkoutSession(int $workoutSessionId): void
    {
        // Get all exercises from this workout session
        $exercises = DB::table('workout_exercises')
            ->join('workout_sessions', 'workout_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('workout_sessions.id', $workoutSessionId)
            ->select('workout_sessions.user_id', 'workout_exercises.exercise_id')
            ->distinct()
            ->get();

        foreach ($exercises as $exercise) {
            /** @var User $user */
            $user = User::findOrFail($exercise->user_id);
            $this->updateRecordsForExercise($user, $exercise->exercise_id);
        }
    }

    /**
     * Backfill personal records for all exercises for a user
     */
    public function backfillAllRecords(User $user): int
    {
        // Get all exercises that have completed sets
        $exerciseIds = DB::table('workout_sets')
            ->join('workout_exercises', 'workout_sets.workout_exercise_id', '=', 'workout_exercises.id')
            ->join('workout_sessions', 'workout_exercises.workout_session_id', '=', 'workout_sessions.id')
            ->where('workout_sessions.user_id', $user->id)
            ->whereNotNull('workout_sessions.ended_at')
            ->select('workout_exercises.exercise_id')
            ->distinct()
            ->pluck('exercise_id');

        $count = 0;
        foreach ($exerciseIds as $exerciseId) {
            $this->updateRecordsForExercise($user, $exerciseId);
            $count++;
        }

        return $count;
    }
}
