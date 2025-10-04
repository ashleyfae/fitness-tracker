<?php

namespace App\Services\Import\Importers;

use App\Models\Exercise;

class RoutineExerciseImporter extends BaseImporter
{
    /**
     * Import routine exercises from CSV
     */
    public function import(): void
    {
        $rows = $this->readCsv();

        foreach ($rows as $index => $row) {
            try {
                $this->importRow($row);
                $this->incrementImported();
            } catch (\Exception $e) {
                $this->logError($index + 2, 'Failed to import routine exercise', $e);
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $row): void
    {
        // Store exercise ID => name mapping for later use
        $this->mapper->mapExerciseName((int) $row['exercise_id'], $row['exercisename']);

        // Get or create exercise
        $exercise = $this->getOrCreateExercise($row['exercisename']);

        // Get routine ID from mapper
        $routineId = $this->mapper->getRoutineId((int) $row['belongplan']);

        // Attach exercise to routine with pivot data
        $this->user->routines()->find($routineId)->exercises()->syncWithoutDetaching([
            $exercise->id => [
                'number_sets' => (int) $row['setcount'],
                'rest_seconds' => (int) $row['timer'],
                'sort' => (int) $row['mysort'],
            ],
        ]);
    }

    /**
     * Get or create exercise by name
     */
    private function getOrCreateExercise(string $exerciseName): Exercise
    {
        return $this->user->exercises()->firstOrCreate(['name' => $exerciseName]);
    }
}
