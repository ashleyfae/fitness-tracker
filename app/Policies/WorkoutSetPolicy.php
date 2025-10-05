<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutSet;

class WorkoutSetPolicy
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
    public function update(User $user, WorkoutSet $workoutSet): bool
    {
        return $user->is($workoutSet->workoutExercise->workoutSession->user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkoutSet $workoutSet): bool
    {
        return $user->is($workoutSet->workoutExercise->workoutSession->user);
    }
}
