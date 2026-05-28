<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\ExerciseGoal;
use App\Models\User;

class ExerciseGoalPolicy
{
    public function create(User $user, Exercise $exercise): bool
    {
        return $user->is($exercise->user);
    }

    public function update(User $user, ExerciseGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    public function delete(User $user, ExerciseGoal $goal): bool
    {
        return $user->id === $goal->user_id;
    }
}
