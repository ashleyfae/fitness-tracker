<?php

namespace App\Services\Import\Importers;

class CustomExerciseImporter extends BaseImporter
{
    /**
     * Import custom exercises from CSV
     */
    public function import(): void
    {
        $rows = $this->readCsv();

        foreach ($rows as $index => $row) {
            try {
                $this->importRow($row);
                $this->incrementImported();
            } catch (\Exception $e) {
                $this->logError($index + 2, 'Failed to import custom exercise', $e); // +2 for header row and 0-index
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $row): void
    {
        $description = $row['description'] ?? null;
        if ($description === '') {
            $description = null;
        }

        $this->user->exercises()->updateOrCreate(
            ['name' => $row['name']],
            [
                'description' => $description,
            ]
        );
    }
}
