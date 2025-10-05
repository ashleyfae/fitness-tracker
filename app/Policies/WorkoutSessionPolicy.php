<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkoutSession;

class WorkoutSessionPolicy
{

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkoutSession $workoutSession): bool
    {
        return $user->is($workoutSession->user);
    }

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
    public function update(User $user, WorkoutSession $workoutSession): bool
    {
        return $user->is($workoutSession->user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkoutSession $workoutSession): bool
    {
        return $user->is($workoutSession->user);
    }
}
