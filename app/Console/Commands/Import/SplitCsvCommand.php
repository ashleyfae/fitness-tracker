<?php

namespace App\Console\Commands\Import;

use App\Services\Import\CsvSplitter;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SplitCsvCommand extends Command
{
    protected $signature = 'import:split-csv
                          {file : Path to the source CSV file}
                          {--output-dir=resources/imports : Output directory for split files}';

    protected $description = 'Split monolithic export CSV into separate files for import';

    public function handle(): int
    {
        $sourceFile = $this->argument('file');
        $outputDir = $this->option('output-dir');

        if (! $this->validateSourceFile($sourceFile)) {
            return 1;
        }

        $this->info("Splitting CSV: {$sourceFile}");
        $this->info("Output directory: {$outputDir}");
        $this->newLine();

        try {
            $splitter = new CsvSplitter($sourceFile, $outputDir);

            $this->withProgressBar(1, function () use ($splitter, &$results) {
                $results = $splitter->split();
            });

            $this->newLine(2);
            $this->displayResults($results);

            $this->newLine();
            $this->info('✓ CSV split completed successfully!');

            return 0;
        } catch (Exception $e) {
            $this->newLine();
            $this->error("Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function validateSourceFile(string $path): bool
    {
        if (! File::exists($path)) {
            $this->error("Error: Source file not found at {$path}");

            return false;
        }

        if (! is_readable($path)) {
            $this->error("Error: Source file is not readable: {$path}");

            return false;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'csv') {
            $this->error('Error: Source file must be a CSV file');

            return false;
        }

        return true;
    }

    private function displayResults(array $results): void
    {
        $tableData = [];

        foreach ($results as $section => $filePath) {
            $rowCount = $this->countCsvRows($filePath);
            $tableData[] = [
                'Section' => $section,
                'Output File' => basename($filePath),
                'Rows' => $rowCount,
            ];
        }

        $this->table(
            ['Section', 'Output File', 'Rows'],
            $tableData
        );
    }

    private function countCsvRows(string $filePath): int
    {
        if (! File::exists($filePath)) {
            return 0;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Subtract 1 for header row
        return count($lines) - 1;
    }
}
