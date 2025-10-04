<?php

namespace App\Services\Import\Importers;

use App\Models\Exercise;
use App\Services\Import\LogsParser;

class ExerciseLogImporter extends BaseImporter
{
    private LogsParser $logsParser;

    private array $importedCombinations = [];

    public function __construct($user, $mapper, $timestampConverter, $csvPath)
    {
        parent::__construct($user, $mapper, $timestampConverter, $csvPath);
        $this->logsParser = new LogsParser;
    }

    /**
     * Import exercise logs from CSV
     */
    public function import(): void
    {
        $rows = $this->readCsv();

        foreach ($rows as $index => $row) {
            try {
                $this->importRow($row);
                $this->incrementImported();
            } catch (\Exception $e) {
                $this->logError($index + 2, 'Failed to import exercise log', $e);
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $row): void
    {
        // Skip logs not associated with a session
        if (empty($row['belongsession']) || $row['belongsession'] === '0') {
            return;
        }

        // Store exercise ID => name mapping if not already present
        if (! $this->mapper->hasExerciseName((int) $row['eid'])) {
            $this->mapper->mapExerciseName((int) $row['eid'], $row['ename']);
        }

        // Get or create exercise
        $exercise = $this->getOrCreateExercise($row['ename']);

        // Get session ID from mapper
        $sessionId = $this->mapper->getSessionId((int) $row['belongsession']);

        // Skip duplicate (session, exercise) combinations
        $combination = "{$sessionId}_{$exercise->id}";
        if (isset($this->importedCombinations[$combination])) {
            return;
        }
        $this->importedCombinations[$combination] = true;

        // Parse logs to get sets
        $sets = $this->logsParser->parse($row['logs']);

        // Create workout exercise
        $workoutExercise = $this->user->workoutSessions()->find($sessionId)->exercises()->create([
            'exercise_id' => $exercise->id,
            'number_sets' => count($sets),
            'rest_seconds' => 60, // Default value
            'sort' => (int) $row['day_item_id'],
        ]);

        // Create workout sets
        $completedAt = $this->timestampConverter->fromUnix((int) $row['logTime']);

        foreach ($sets as $setData) {
            $workoutExercise->sets()->create([
                'weight_kg' => $setData['weight_kg'],
                'number_reps' => $setData['number_reps'],
                'completed_at' => $completedAt,
            ]);
        }
    }

    /**
     * Get or create exercise by name
     */
    private function getOrCreateExercise(string $exerciseName): Exercise
    {
        return $this->user->exercises()->firstOrCreate(['name' => $exerciseName]);
    }
}
