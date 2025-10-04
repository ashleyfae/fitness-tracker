<?php

namespace App\Services\Import\Importers;

class WorkoutSessionImporter extends BaseImporter
{
    /**
     * Import workout sessions from CSV
     */
    public function import(): void
    {
        $rows = $this->readCsv();

        foreach ($rows as $index => $row) {
            try {
                $this->importRow($row);
                $this->incrementImported();
            } catch (\Exception $e) {
                $this->logError($index + 2, 'Failed to import workout session', $e);
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $row): void
    {
        // Get routine ID from mapper (may be null if session not linked to routine)
        $routineId = null;
        if (! empty($row['day_id']) && $this->mapper->hasRoutine((int) $row['day_id'])) {
            $routineId = $this->mapper->getRoutineId((int) $row['day_id']);
        }

        $session = $this->user->workoutSessions()->create([
            'routine_id' => $routineId,
            'started_at' => $this->timestampConverter->fromUnix((int) $row['starttime']),
            'ended_at' => $this->timestampConverter->fromUnix((int) $row['endtime']),
            'duration_seconds' => (int) $row['total_time'],
            'total_exercises' => (int) $row['total_exercise'],
            'total_kg_lifted' => (float) $row['total_weight'],
        ]);

        // Map old session ID to new session ID
        $this->mapper->mapSession((int) $row['_id'], $session->id);
    }
}
