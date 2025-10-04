<?php

/**
 * UpdateRoutine.php
 *
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace App\Actions\Routines;

use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;

class UpdateRoutine
{
    public function execute(Routine $routine, UpdateRoutineRequest $request): Routine
    {
        $data = $request->validated();

        $routine->update($data);
        $routine->exercises()->sync($this->normalizeExercises($data['exercises'] ?? []));

        return $routine;
    }

    protected function normalizeExercises(array $exercises): array
    {
        // Sort exercises by their sort value
        uasort($exercises, function ($a, $b) {
            return $a['sort'] <=> $b['sort'];
        });

        // Reassign sequential sort values
        $counter = 0;
        $normalized = [];

        foreach ($exercises as $exerciseId => $exercise) {
            $exercise['sort'] = $counter++;
            $normalized[$exerciseId] = $exercise;
        }

        return $normalized;
    }
}
