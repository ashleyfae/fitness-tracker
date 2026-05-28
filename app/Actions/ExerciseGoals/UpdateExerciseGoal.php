<?php

namespace App\Actions\ExerciseGoals;

use App\Models\ExerciseGoal;

class UpdateExerciseGoal
{
    public function execute(ExerciseGoal $goal, array $data): ExerciseGoal
    {
        $goal->update([
            'target_sets'      => $data['target_sets'],
            'target_weight_kg' => $data['target_weight_kg'],
            'target_reps'      => $data['target_reps'],
        ]);

        return $goal;
    }
}
