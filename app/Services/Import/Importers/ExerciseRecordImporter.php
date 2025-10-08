<?php

namespace App\Services\Import\Importers;

use App\Models\Exercise;

class ExerciseRecordImporter extends BaseImporter
{
    /**
     * Import exercise records from CSV
     */
    public function import(): void
    {
        $rows = $this->readCsv();

        foreach ($rows as $index => $row) {
            try {
                $this->importRow($row);
                $this->incrementImported();
            } catch (\Exception $e) {
                $this->logError($index + 2, 'Failed to import exercise record', $e);
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $row): void
    {
        // Get exercise name from mapper (populated by earlier importers)
        $exerciseName = $this->mapper->getExerciseName((int) $row['eid']);

        // Get or create exercise
        $exercise = $this->getOrCreateExercise($exerciseName);

        // Create exercise record
        $this->user->exerciseRecords()->create([
            'exercise_id' => $exercise->id,
            'estimated_1rm_kg' => (float) $row['record'], // This was the old app's calculated 1RM
            'best_weight_kg' => null, // Will be calculated from actual workout data
            'achieved_at' => $this->timestampConverter->fromUnix((int) $row['recordReachTime']),
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
