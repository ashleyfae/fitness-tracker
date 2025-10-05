<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutExercise;

class WorkoutExercisePolicy
{

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkoutExercise $workoutExercise): bool
    {
        return $user->is($workoutExercise->workoutSession->user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkoutExercise $workoutExercise): bool
    {
        return $user->is($workoutExercise->workoutSession->user);
    }
}
