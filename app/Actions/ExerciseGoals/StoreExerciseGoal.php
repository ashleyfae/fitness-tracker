<?php

namespace App\Actions\ExerciseGoals;

use App\Models\Exercise;
use App\Models\ExerciseGoal;

class StoreExerciseGoal
{
    public function execute(Exercise $exercise, array $data): ExerciseGoal
    {
        $maxSort = $exercise->goals()->whereNull('completed_at')->max('sort') ?? -1;

        return $exercise->goals()->create([
            'user_id'          => $exercise->user_id,
            'sort'             => $maxSort + 1,
            'target_sets'      => $data['target_sets'],
            'target_weight_kg' => $data['target_weight_kg'],
            'target_reps'      => $data['target_reps'],
        ]);
    }
}
