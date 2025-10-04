<?php

namespace App\Services\Import\Importers;

class RoutineDayImporter extends BaseImporter
{
    /**
     * Import routine days from CSV
     */
    public function import(): void
    {
        $rows = $this->readCsv();

        foreach ($rows as $index => $row) {
            try {
                $this->importRow($row);
                $this->incrementImported();
            } catch (\Exception $e) {
                $this->logError($index + 2, 'Failed to import routine day', $e);
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $row): void
    {
        $routine = $this->user->routines()->create([
            'name' => $row['name'],
        ]);

        // Map old routine day ID to new routine ID
        $this->mapper->mapRoutine((int) $row['_id'], $routine->id);
    }
}
