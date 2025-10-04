<?php

namespace App\Console\Commands\Import;

use App\Models\User;
use App\Services\Import\IdMapper;
use App\Services\Import\Importers\BaseImporter;
use App\Services\Import\Importers\CustomExerciseImporter;
use App\Services\Import\Importers\ExerciseLogImporter;
use App\Services\Import\Importers\ExerciseRecordImporter;
use App\Services\Import\Importers\RoutineDayImporter;
use App\Services\Import\Importers\RoutineExerciseImporter;
use App\Services\Import\Importers\WorkoutSessionImporter;
use App\Services\Import\TimestampConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'import:data
                          {--user=1 : The user ID to import data for}
                          {--input-dir=resources/imports : Directory containing split CSV files}
                          {--dry-run : Validate without importing}';

    /**
     * The console command description.
     */
    protected $description = 'Import fitness data from split CSV files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = (int) $this->option('user');
        $inputDir = $this->option('input-dir');
        $dryRun = $this->option('dry-run');

        // Validate user exists
        /** @var User|null $user */
        $user = User::find($userId);
        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        // Validate input directory exists
        if (! File::isDirectory($inputDir)) {
            $this->error("Input directory not found: {$inputDir}");

            return self::FAILURE;
        }

        // Define expected CSV files
        $csvFiles = [
            'custom-exercises' => "{$inputDir}/custom-exercises.csv",
            'routine-days' => "{$inputDir}/routine-days.csv",
            'routine-exercises' => "{$inputDir}/routine-exercises.csv",
            'workout-sessions' => "{$inputDir}/workout-sessions.csv",
            'exercise-logs' => "{$inputDir}/exercise-logs.csv",
            'exercise-records' => "{$inputDir}/exercise-records.csv",
        ];

        // Validate CSV files exist
        foreach ($csvFiles as $key => $path) {
            if (! File::exists($path)) {
                $this->error("Required CSV file not found: {$path}");

                return self::FAILURE;
            }
        }

        // Confirm before proceeding
        if (! $dryRun) {
            $confirmed = $this->confirm(
                "This will import data for user {$user->name} (ID: {$userId}). Continue?",
                true
            );

            if (! $confirmed) {
                $this->info('Import cancelled.');

                return self::SUCCESS;
            }
        }

        $this->info($dryRun ? 'Running in dry-run mode...' : 'Starting import...');
        $this->newLine();

        // Track overall statistics
        $totalImported = 0;
        $totalErrors = 0;

        // Begin transaction
        DB::beginTransaction();

        try {
            $this->runImporters($user, $csvFiles, new IdMapper, new TimestampConverter, $totalImported, $totalErrors);

            // Commit or rollback based on dry-run mode
            if ($dryRun) {
                $this->info('Dry-run complete. Rolling back changes...');
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Display summary
        $this->newLine();
        $this->info('Import Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Records Imported', $totalImported],
                ['Total Errors', $totalErrors],
            ]
        );

        if ($dryRun) {
            $this->info('Dry-run completed successfully. No data was persisted.');
        } else {
            $this->info('Import completed successfully!');
        }

        return self::SUCCESS;
    }

    /**
     * Run all importers in sequence
     */
    private function runImporters(
        User $user,
        array $csvFiles,
        IdMapper $mapper,
        TimestampConverter $timestampConverter,
        int &$totalImported,
        int &$totalErrors
    ): void {
        $importers = [
            [
                'class' => CustomExerciseImporter::class,
                'csv' => $csvFiles['custom-exercises'],
                'label' => 'custom exercises',
            ],
            [
                'class' => RoutineDayImporter::class,
                'csv' => $csvFiles['routine-days'],
                'label' => 'routine days',
            ],
            [
                'class' => RoutineExerciseImporter::class,
                'csv' => $csvFiles['routine-exercises'],
                'label' => 'routine exercises',
            ],
            [
                'class' => WorkoutSessionImporter::class,
                'csv' => $csvFiles['workout-sessions'],
                'label' => 'workout sessions',
            ],
            [
                'class' => ExerciseLogImporter::class,
                'csv' => $csvFiles['exercise-logs'],
                'label' => 'exercise logs',
            ],
            [
                'class' => ExerciseRecordImporter::class,
                'csv' => $csvFiles['exercise-records'],
                'label' => 'exercise records',
            ],
        ];

        foreach ($importers as $config) {
            $importer = new $config['class']($user, $mapper, $timestampConverter, $config['csv']);
            $this->runImporter($importer, $config['label'], $totalImported, $totalErrors);
        }
    }

    /**
     * Run a single importer and display results
     */
    private function runImporter(
        BaseImporter $importer,
        string $label,
        int &$totalImported,
        int &$totalErrors
    ): void {
        $this->info("Importing {$label}...");

        $importer->import();

        $imported = $importer->getImportedCount();
        $errors = $importer->getErrors();

        $totalImported += $imported;
        $totalErrors += count($errors);

        $this->line("  ✓ Imported {$imported} {$label}");

        if (count($errors) > 0) {
            $errorWord = Str::plural('error', count($errors));
            $this->warn('  ⚠ '.count($errors)." {$errorWord} occurred");

            // Show first 10 errors
            $displayErrors = array_slice($errors, 0, 10);
            foreach ($displayErrors as $error) {
                $message = $error['message'];
                if (isset($error['exception'])) {
                    $message .= ': '.$error['exception'];
                }
                $this->line("    - Row {$error['row']}: {$message}");
            }

            if (count($errors) > 10) {
                $this->line('    ... and '.(count($errors) - 10).' more errors');
            }
        }

        $this->newLine();
    }
}
