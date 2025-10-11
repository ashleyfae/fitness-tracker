<?php

namespace App\Services\Import\Importers;

use App\Models\User;
use App\Services\Import\IdMapper;
use App\Services\Import\TimestampConverter;
use Exception;
use Illuminate\Support\Facades\File;

abstract class BaseImporter
{
    protected User $user;

    protected IdMapper $mapper;

    protected TimestampConverter $timestampConverter;

    protected string $csvPath;

    protected int $importedCount = 0;

    protected array $errors = [];

    public function __construct(
        User $user,
        IdMapper $mapper,
        TimestampConverter $timestampConverter,
        string $csvPath
    ) {
        $this->user = $user;
        $this->mapper = $mapper;
        $this->timestampConverter = $timestampConverter;
        $this->csvPath = $csvPath;

        $this->validateCsvExists();
    }

    /**
     * Run the import process
     */
    abstract public function import(): void;

    /**
     * Get the number of records imported
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get any errors that occurred during import
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Read and parse CSV file
     */
    protected function readCsv(): array
    {
        $rows = [];
        $handle = fopen($this->csvPath, 'r');

        if ($handle === false) {
            throw new Exception("Unable to open CSV file: {$this->csvPath}");
        }

        // Read header row
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            throw new Exception("CSV file is empty: {$this->csvPath}");
        }

        // Read data rows
        while (($data = fgetcsv($handle)) !== false) {
            // Strip trailing empty fields (caused by trailing commas in CSV)
            while (count($data) > count($header) && end($data) === '') {
                array_pop($data);
            }

            if (count($data) === count($header)) {
                $rows[] = array_combine($header, $data);
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Validate that the CSV file exists
     */
    protected function validateCsvExists(): void
    {
        if (! File::exists($this->csvPath)) {
            throw new Exception("CSV file not found: {$this->csvPath}");
        }
    }

    /**
     * Log an error during import
     */
    protected function logError(int $rowNumber, string $message, ?\Throwable $exception = null): void
    {
        $error = [
            'row' => $rowNumber,
            'message' => $message,
        ];

        if ($exception) {
            $error['exception'] = $exception->getMessage();
        }

        $this->errors[] = $error;
    }

    /**
     * Increment the imported count
     */
    protected function incrementImported(): void
    {
        $this->importedCount++;
    }
}
