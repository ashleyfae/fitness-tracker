# Import Implementation Plan

This document provides a step-by-step implementation checklist for the data import system. Follow the phases in order, completing all tests before moving to the next phase.

---

## Phase 1: CSV Splitter

**Goal:** Split monolithic export CSV into separate files for each data section.

### Files to Create

#### 1. `app/Services/Import/CsvSplitter.php`

```php
<?php

namespace App\Services\Import;

class CsvSplitter
{
    private const SECTION_MARKERS = [
        'ROUTINES' => '### ROUTINES',
        'WORKOUT_SESSIONS' => '### WORKOUT SESSIONS',
        'EXERCISE_LOGS' => '### EXERCISE LOGS',
        'EXERCISE_RECORDS' => '### EXERCISE RECORDS',
        'CUSTOM_EXERCISES' => '### CUSTOM EXERCISES',
    ];

    public function __construct(
        private string $sourcePath,
        private string $outputDirectory
    ) {}

    /**
     * Split the monolithic CSV into separate files
     *
     * @return array Map of section name => output file path
     */
    public function split(): array;

    /**
     * Read source file and identify sections
     *
     * @return array Map of section name => [start_line, end_line]
     */
    private function identifySections(): array;

    /**
     * Extract specific section from source file
     */
    private function extractSection(string $sectionName, int $startLine, int $endLine): void;

    /**
     * Parse routine section (has 3 tiers)
     */
    private function extractRoutineSection(int $startLine, int $endLine): void;

    /**
     * Find headers within a section
     */
    private function findSectionHeaders(int $startLine, int $endLine): array;

    /**
     * Write CSV data to output file
     */
    private function writeCsv(string $filename, array $headers, array $rows): string;

    /**
     * Get output filename for section
     */
    private function getOutputFilename(string $sectionName): string;
}
```

**Key Logic:**
- Routine section needs special handling (split tier 2 and tier 3)
- Output files: `custom-exercises.csv`, `routine-days.csv`, `routine-exercises.csv`, `workout-sessions.csv`, `exercise-logs.csv`, `exercise-records.csv`
- Handle blank lines between sections
- Validate each section has headers + data rows

#### 2. `app/Console/Commands/Import/SplitCsvCommand.php`

```php
<?php

namespace App\Console\Commands\Import;

use App\Services\Import\CsvSplitter;
use Illuminate\Console\Command;

class SplitCsvCommand extends Command
{
    protected $signature = 'import:split-csv
                          {file : Path to the source CSV file}
                          {--output-dir=resources/imports : Output directory for split files}';

    protected $description = 'Split monolithic export CSV into separate files for import';

    public function handle(): int;

    private function validateSourceFile(string $path): bool;

    private function displayResults(array $results): void;
}
```

**Features:**
- Validate source file exists
- Show progress while splitting
- Display table of results (section name, output file, row count)
- Error handling with helpful messages

### Tests to Write

#### `tests/Unit/Services/Import/CsvSplitterTest.php`

```php
<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\CsvSplitter;
use Tests\TestCase;

class CsvSplitterTest extends TestCase
{
    private string $testCsvPath;
    private string $outputDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputDir = storage_path('testing/imports');
        // Create test output directory
    }

    protected function tearDown(): void
    {
        // Clean up test files
        parent::tearDown();
    }

    /** @test */
    public function it_identifies_all_sections(): void
    {
        // Create minimal test CSV with all section markers
        // Assert sections are identified correctly
    }

    /** @test */
    public function it_splits_custom_exercises_section(): void
    {
        // Test CSV with CUSTOM EXERCISES section
        // Assert output file created with correct headers + rows
    }

    /** @test */
    public function it_splits_routine_section_into_two_files(): void
    {
        // Test CSV with ROUTINES section (3 tiers)
        // Assert routine-days.csv created
        // Assert routine-exercises.csv created
        // Assert correct row counts
    }

    /** @test */
    public function it_splits_workout_sessions_section(): void
    {
        // Test workout sessions section
    }

    /** @test */
    public function it_splits_exercise_logs_section(): void
    {
        // Test exercise logs section
    }

    /** @test */
    public function it_splits_exercise_records_section(): void
    {
        // Test exercise records section
    }

    /** @test */
    public function it_handles_empty_sections(): void
    {
        // Test CSV with section marker but no data
        // Assert file still created with headers
    }

    /** @test */
    public function it_returns_results_map(): void
    {
        // Assert split() returns array with section => filepath
    }
}
```

#### `tests/Feature/Console/Commands/Import/SplitCsvCommandTest.php`

```php
<?php

namespace Tests\Feature\Console\Commands\Import;

use Tests\TestCase;

class SplitCsvCommandTest extends TestCase
{
    /** @test */
    public function it_requires_file_argument(): void
    {
        $this->artisan('import:split-csv')
            ->assertFailed();
    }

    /** @test */
    public function it_validates_source_file_exists(): void
    {
        $this->artisan('import:split-csv', ['file' => 'nonexistent.csv'])
            ->expectsOutput('Error: Source file not found')
            ->assertFailed();
    }

    /** @test */
    public function it_splits_csv_successfully(): void
    {
        // Create test CSV file
        // Run command
        // Assert success message
        // Assert output files exist
    }

    /** @test */
    public function it_displays_results_table(): void
    {
        // Run command
        // Assert table output shown
    }
}
```

### Acceptance Criteria

- [ ] `CsvSplitter` class created with all methods
- [ ] `SplitCsvCommand` created
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Manual test: Run command on real CSV export, verify all 6 files created
- [ ] Code formatted with Laravel Pint

---

## Phase 2: Exercise Importer (Proof of Concept)

**Goal:** Build foundational services and prove the pattern works with one importer.

### Files to Create

#### 1. `app/Services/Import/IdMapper.php`

```php
<?php

namespace App\Services\Import;

class IdMapper
{
    private array $exerciseMap = [];
    private array $routineMap = [];
    private array $sessionMap = [];

    // Exercise mappings
    public function mapExercise(int $oldId, int $newId): void;
    public function getExerciseId(int $oldId): int;
    public function hasExercise(int $oldId): bool;

    // Routine mappings
    public function mapRoutine(int $oldId, int $newId): void;
    public function getRoutineId(int $oldId): int;
    public function hasRoutine(int $oldId): bool;

    // Session mappings
    public function mapSession(int $oldId, int $newId): void;
    public function getSessionId(int $oldId): int;
    public function hasSession(int $oldId): bool;

    // Utility
    public function clear(): void;
    public function getCounts(): array;
}
```

**Features:**
- Throw exception if old ID not found in map
- `getCounts()` returns stats for each map type

#### 2. `app/Services/Import/TimestampConverter.php`

```php
<?php

namespace App\Services\Import;

use Illuminate\Support\Carbon;

class TimestampConverter
{
    /**
     * Convert Unix timestamp to Carbon instance
     */
    public function fromUnix(int $timestamp): Carbon;

    /**
     * Convert date string to Carbon instance
     */
    public function fromDateString(string $date): Carbon;
}
```

#### 3. `app/Services/Import/Importers/BaseImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\User;
use App\Services\Import\IdMapper;
use Illuminate\Support\Collection;

abstract class BaseImporter
{
    protected string $csvFilename;

    /**
     * Validate CSV file and data
     */
    abstract public function validate(): bool;

    /**
     * Import data from CSV
     *
     * @return int Number of records imported
     */
    abstract public function import(User $user, IdMapper $mapper): int;

    /**
     * Transform CSV row into model attributes
     */
    abstract protected function transformRow(array $row): array;

    /**
     * Read CSV file into collection
     */
    protected function readCsv(): Collection;

    /**
     * Get full path to CSV file
     */
    protected function getCsvPath(): string;

    /**
     * Check if CSV file exists
     */
    protected function csvExists(): bool;

    /**
     * Validate CSV has required columns
     */
    protected function hasRequiredColumns(array $required): bool;
}
```

#### 4. `app/Services/Import/Importers/CustomExerciseImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\Exercise;
use App\Models\User;
use App\Services\Import\IdMapper;
use Illuminate\Support\Collection;

class CustomExerciseImporter extends BaseImporter
{
    protected string $csvFilename = 'custom-exercises.csv';

    public function validate(): bool;

    public function import(User $user, IdMapper $mapper): int;

    protected function transformRow(array $row): array;

    /**
     * Persist exercise to database
     */
    private function persistExercise(User $user, array $attributes): Exercise;
}
```

**Implementation:**
- Required columns: `_id`, `name`
- Optional columns: `description`
- Skip image/bodypart fields for now
- Store old `_id` → new `id` in IdMapper

#### 5. `app/Console/Commands/Import/ImportDataCommand.php`

```php
<?php

namespace App\Console\Commands\Import;

use App\Models\User;
use App\Services\Import\IdMapper;
use App\Services\Import\Importers\BaseImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDataCommand extends Command
{
    protected $signature = 'import:data
                          {--user=1 : User ID to assign imported data to}
                          {--dry-run : Validate without importing}';

    protected $description = 'Import historical workout data from split CSV files';

    public function handle(): int;

    /**
     * Get user for import
     */
    private function getUser(): User;

    /**
     * Get importers in dependency order
     *
     * @return BaseImporter[]
     */
    private function getImporters(): array;

    /**
     * Validate all importers
     */
    private function validateAll(array $importers): bool;

    /**
     * Run single importer with progress
     */
    private function runImporter(BaseImporter $importer, User $user, IdMapper $mapper): int;

    /**
     * Display import summary
     */
    private function displaySummary(IdMapper $mapper, array $counts): void;
}
```

**Features (Phase 2):**
- Only use CustomExerciseImporter for now
- Show progress bar
- Use DB transaction (rollback on error)
- Dry-run mode: validate only, don't persist
- Display summary table

### Tests to Write

#### `tests/Unit/Services/Import/IdMapperTest.php`

```php
<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\IdMapper;
use Tests\TestCase;

class IdMapperTest extends TestCase
{
    private IdMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new IdMapper();
    }

    /** @test */
    public function it_maps_exercise_ids(): void
    {
        $this->mapper->mapExercise(12, 1);

        $this->assertEquals(1, $this->mapper->getExerciseId(12));
        $this->assertTrue($this->mapper->hasExercise(12));
    }

    /** @test */
    public function it_throws_exception_for_unmapped_exercise(): void
    {
        $this->expectException(\Exception::class);
        $this->mapper->getExerciseId(999);
    }

    /** @test */
    public function it_maps_routine_ids(): void
    {
        // Similar to exercise test
    }

    /** @test */
    public function it_maps_session_ids(): void
    {
        // Similar to exercise test
    }

    /** @test */
    public function it_returns_counts(): void
    {
        $this->mapper->mapExercise(1, 10);
        $this->mapper->mapRoutine(2, 20);

        $counts = $this->mapper->getCounts();

        $this->assertEquals(1, $counts['exercises']);
        $this->assertEquals(1, $counts['routines']);
        $this->assertEquals(0, $counts['sessions']);
    }

    /** @test */
    public function it_clears_all_maps(): void
    {
        $this->mapper->mapExercise(1, 10);
        $this->mapper->clear();

        $this->assertFalse($this->mapper->hasExercise(1));
    }
}
```

#### `tests/Unit/Services/Import/TimestampConverterTest.php`

```php
<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\TimestampConverter;
use Tests\TestCase;

class TimestampConverterTest extends TestCase
{
    private TimestampConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new TimestampConverter();
    }

    /** @test */
    public function it_converts_unix_timestamp(): void
    {
        $carbon = $this->converter->fromUnix(1456180840);

        $this->assertEquals('2016-02-22', $carbon->format('Y-m-d'));
    }

    /** @test */
    public function it_converts_date_string(): void
    {
        $carbon = $this->converter->fromDateString('2016-02-22');

        $this->assertEquals('2016-02-22', $carbon->format('Y-m-d'));
    }
}
```

#### `tests/Unit/Services/Import/Importers/CustomExerciseImporterTest.php`

```php
<?php

namespace Tests\Unit\Services\Import\Importers;

use App\Models\User;
use App\Services\Import\IdMapper;
use App\Services\Import\Importers\CustomExerciseImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomExerciseImporterTest extends TestCase
{
    use RefreshDatabase;

    private CustomExerciseImporter $importer;
    private User $user;
    private IdMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importer = new CustomExerciseImporter();
        $this->user = User::factory()->create();
        $this->mapper = new IdMapper();

        // Create test CSV file
        $this->createTestCsv();
    }

    /** @test */
    public function it_validates_csv_exists(): void
    {
        // Test validation
    }

    /** @test */
    public function it_validates_required_columns(): void
    {
        // Test validation with missing columns
    }

    /** @test */
    public function it_imports_exercises(): void
    {
        $count = $this->importer->import($this->user, $this->mapper);

        $this->assertEquals(3, $count); // If test CSV has 3 rows
        $this->assertDatabaseCount('exercises', 3);
    }

    /** @test */
    public function it_maps_old_ids_to_new_ids(): void
    {
        $this->importer->import($this->user, $this->mapper);

        $this->assertTrue($this->mapper->hasExercise(11)); // old ID from CSV
    }

    /** @test */
    public function it_transforms_row_correctly(): void
    {
        $this->importer->import($this->user, $this->mapper);

        $this->assertDatabaseHas('exercises', [
            'name' => 'Barbell Shrug',
            'user_id' => $this->user->id,
        ]);
    }

    private function createTestCsv(): void
    {
        // Create resources/imports/custom-exercises.csv with test data
    }
}
```

#### `tests/Feature/Console/Commands/Import/ImportDataCommandTest.php`

```php
<?php

namespace Tests\Feature\Console\Commands\Import;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportDataCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_requires_valid_user(): void
    {
        $this->artisan('import:data', ['--user' => 999])
            ->expectsOutput('Error: User not found')
            ->assertFailed();
    }

    /** @test */
    public function it_imports_exercises(): void
    {
        $user = User::factory()->create();
        // Create test CSV

        $this->artisan('import:data', ['--user' => $user->id])
            ->assertSuccessful();

        $this->assertDatabaseCount('exercises', 3); // Based on test CSV
    }

    /** @test */
    public function it_runs_in_dry_run_mode(): void
    {
        $user = User::factory()->create();
        // Create test CSV

        $this->artisan('import:data', ['--user' => $user->id, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('exercises', 0); // Nothing persisted
    }

    /** @test */
    public function it_displays_summary(): void
    {
        $user = User::factory()->create();

        $this->artisan('import:data', ['--user' => $user->id])
            ->expectsTable(/* verify summary table */);
    }

    /** @test */
    public function it_rolls_back_on_error(): void
    {
        // Test with invalid CSV data
        // Assert no records persisted
    }
}
```

### Acceptance Criteria

- [ ] `IdMapper` created and tested
- [ ] `TimestampConverter` created and tested
- [ ] `BaseImporter` abstract class created
- [ ] `CustomExerciseImporter` created and tested
- [ ] `ImportDataCommand` created (exercises only)
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Manual test: Import real custom-exercises.csv, verify exercises in database
- [ ] Manual test: Verify ID mapping works correctly
- [ ] Manual test: Dry-run mode works
- [ ] Code formatted with Laravel Pint

---

## Phase 3: Remaining Importers

**Goal:** Complete all importers following the proven pattern.

### Files to Create

#### 1. `app/Services/Import/LogsParser.php`

```php
<?php

namespace App\Services\Import;

class LogsParser
{
    /**
     * Parse logs string into array of sets
     *
     * Input: "22.5x5,22.5x8,22.5x8"
     * Output: [
     *   ['weight_kg' => 22.5, 'number_reps' => 5],
     *   ['weight_kg' => 22.5, 'number_reps' => 8],
     *   ['weight_kg' => 22.5, 'number_reps' => 8],
     * ]
     */
    public function parse(string $logs): array;

    /**
     * Split logs into individual set strings
     */
    private function splitIntoSets(string $logs): array;

    /**
     * Parse single set string (e.g., "22.5x8")
     */
    private function parseSet(string $set): array;

    /**
     * Check if set is time-based (e.g., "0x5x30")
     */
    private function isTimeBased(string $set): bool;

    /**
     * Parse time-based set (special handling)
     */
    private function parseTimeBased(string $set): array;
}
```

**Edge Cases:**
- Empty logs: `0x0` or empty string → return empty array
- Time-based: `0x5x30` → may need to skip or handle specially
- Bodyweight: `0.5x10` → weight_kg = 0.5

#### 2. `app/Services/Import/ImportValidator.php`

```php
<?php

namespace App\Services\Import;

class ImportValidator
{
    /**
     * Validate all required CSV files exist
     */
    public function validateCsvFilesExist(array $filenames): bool;

    /**
     * Validate user exists
     */
    public function validateUser(int $userId): bool;

    /**
     * Validate CSV has required columns
     */
    public function validateColumns(string $csvPath, array $requiredColumns): bool;

    /**
     * Get validation errors
     */
    public function getErrors(): array;
}
```

#### 3. `app/Services/Import/Importers/RoutineDayImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\Routine;
use App\Models\User;
use App\Services\Import\IdMapper;

class RoutineDayImporter extends BaseImporter
{
    protected string $csvFilename = 'routine-days.csv';

    public function validate(): bool;
    public function import(User $user, IdMapper $mapper): int;
    protected function transformRow(array $row): array;
    private function persistRoutine(User $user, array $attributes): Routine;
}
```

**Mapping:**
- `_id` → store in IdMapper
- `name` → routine name

#### 4. `app/Services/Import/Importers/RoutineExerciseImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\User;
use App\Services\Import\IdMapper;

class RoutineExerciseImporter extends BaseImporter
{
    protected string $csvFilename = 'routine-exercises.csv';

    public function validate(): bool;
    public function import(User $user, IdMapper $mapper): int;
    protected function transformRow(array $row): array;

    /**
     * Attach exercise to routine with pivot data
     */
    private function attachExercise(int $routineId, int $exerciseId, array $pivotData): void;
}
```

**Mapping:**
- `belongplan` → lookup routine_id via IdMapper
- `exercise_id` → lookup new exercise_id via IdMapper
- `setcount` → number_sets
- `timer` → rest_seconds
- `mysort` → sort

#### 5. `app/Services/Import/Importers/WorkoutSessionImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\User;
use App\Models\WorkoutSession;
use App\Services\Import\IdMapper;
use App\Services\Import\TimestampConverter;

class WorkoutSessionImporter extends BaseImporter
{
    protected string $csvFilename = 'workout-sessions.csv';

    public function __construct(
        private TimestampConverter $timestampConverter
    ) {}

    public function validate(): bool;
    public function import(User $user, IdMapper $mapper): int;
    protected function transformRow(array $row): array;
    private function persistSession(User $user, array $attributes): WorkoutSession;
}
```

**Mapping:**
- `_id` → store in IdMapper
- `day_id` → lookup routine_id via IdMapper
- `starttime` → convert to Carbon for started_at
- `endtime` → convert to Carbon for ended_at
- `total_time` → duration_seconds
- `total_exercise` → total_exercises
- `total_weight` → total_kg_lifted

#### 6. `app/Services/Import/Importers/ExerciseLogImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\User;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use App\Services\Import\IdMapper;
use App\Services\Import\LogsParser;
use App\Services\Import\TimestampConverter;

class ExerciseLogImporter extends BaseImporter
{
    protected string $csvFilename = 'exercise-logs.csv';

    public function __construct(
        private LogsParser $logsParser,
        private TimestampConverter $timestampConverter
    ) {}

    public function validate(): bool;
    public function import(User $user, IdMapper $mapper): int;
    protected function transformRow(array $row): array;

    /**
     * Create WorkoutExercise record
     */
    private function createWorkoutExercise(array $row, IdMapper $mapper): WorkoutExercise;

    /**
     * Create WorkoutSet records from logs field
     */
    private function createWorkoutSets(WorkoutExercise $workoutExercise, array $row): void;

    /**
     * Persist single workout set
     */
    private function persistWorkoutSet(
        WorkoutExercise $workoutExercise,
        array $setData,
        int $completedAt
    ): WorkoutSet;
}
```

**Mapping:**
- `belongsession` → lookup workout_session_id via IdMapper
- `eid` → lookup exercise_id via IdMapper
- `logs` → parse to get number_sets + create WorkoutSet records
- `day_item_id` → use for sort order
- Default `rest_seconds` to 60

#### 7. `app/Services/Import/Importers/ExerciseRecordImporter.php`

```php
<?php

namespace App\Services\Import\Importers;

use App\Models\ExerciseRecord;
use App\Models\User;
use App\Services\Import\IdMapper;
use App\Services\Import\TimestampConverter;

class ExerciseRecordImporter extends BaseImporter
{
    protected string $csvFilename = 'exercise-records.csv';

    public function __construct(
        private TimestampConverter $timestampConverter
    ) {}

    public function validate(): bool;
    public function import(User $user, IdMapper $mapper): int;
    protected function transformRow(array $row): array;
    private function persistRecord(User $user, array $attributes): ExerciseRecord;
}
```

**Mapping:**
- `eid` → lookup exercise_id via IdMapper
- `record` → best_weight_kg
- `recordReachTime` → convert to Carbon for achieved_at

### Update ImportDataCommand

Add all importers to `getImporters()` method in correct order:

```php
private function getImporters(): array
{
    return [
        app(CustomExerciseImporter::class),
        app(RoutineDayImporter::class),
        app(RoutineExerciseImporter::class),
        app(WorkoutSessionImporter::class),
        app(ExerciseLogImporter::class),
        app(ExerciseRecordImporter::class),
    ];
}
```

### Tests to Write

#### For Each Importer (6 total):
- Unit test: validation
- Unit test: row transformation
- Unit test: import creates records
- Unit test: ID mapping works
- Integration test: full import with dependencies

#### `tests/Unit/Services/Import/LogsParserTest.php`

```php
<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\LogsParser;
use Tests\TestCase;

class LogsParserTest extends TestCase
{
    private LogsParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new LogsParser();
    }

    /** @test */
    public function it_parses_standard_logs(): void
    {
        $result = $this->parser->parse("22.5x5,22.5x8,22.5x8");

        $this->assertCount(3, $result);
        $this->assertEquals(22.5, $result[0]['weight_kg']);
        $this->assertEquals(5, $result[0]['number_reps']);
    }

    /** @test */
    public function it_handles_bodyweight_exercises(): void
    {
        $result = $this->parser->parse("0.5x10,0.5x8");

        $this->assertEquals(0.5, $result[0]['weight_kg']);
    }

    /** @test */
    public function it_handles_empty_logs(): void
    {
        $result = $this->parser->parse("0x0");

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_detects_time_based_sets(): void
    {
        // Test "0x5x30" format
    }
}
```

### Acceptance Criteria

- [ ] `LogsParser` created and tested
- [ ] `ImportValidator` created and tested
- [ ] `RoutineDayImporter` created and tested
- [ ] `RoutineExerciseImporter` created and tested
- [ ] `WorkoutSessionImporter` created and tested
- [ ] `ExerciseLogImporter` created and tested
- [ ] `ExerciseRecordImporter` created and tested
- [ ] `ImportDataCommand` updated with all importers
- [ ] All unit tests pass (minimum 80% code coverage)
- [ ] All feature tests pass
- [ ] Manual test: Full import of real data
- [ ] Manual test: Verify all relationships intact (sessions → exercises → sets)
- [ ] Manual test: Spot-check imported data matches source CSV
- [ ] Code formatted with Laravel Pint
- [ ] Update `CLAUDE.md` with import command documentation

---

## Testing Strategy

### Unit Tests
- Test each service in isolation
- Mock dependencies
- Focus on single responsibility

### Integration Tests
- Test importers with real database
- Use RefreshDatabase trait
- Create minimal test CSV files

### Manual Testing Checklist

**After Phase 1:**
- [ ] Run split command on real export CSV
- [ ] Verify 6 output files created
- [ ] Open each file, verify headers and row counts

**After Phase 2:**
- [ ] Import custom exercises
- [ ] Check database: exercises table
- [ ] Verify user_id assigned correctly
- [ ] Check ID mapping works

**After Phase 3:**
- [ ] Full import of all data
- [ ] Query database: verify counts match CSV
- [ ] Test relationships:
  - [ ] Routine → Exercises (via pivot)
  - [ ] WorkoutSession → Routine
  - [ ] WorkoutSession → WorkoutExercises → WorkoutSets
  - [ ] User → ExerciseRecords → Exercise
- [ ] Spot-check 5-10 random records against CSV
- [ ] Verify PRs imported correctly

---

## Implementation Notes

### CSV File Location
All split CSV files in: `resources/imports/`

### Test Data Location
Test CSV files in: `tests/Fixtures/imports/`

### Logging
Use Laravel Log facade:
```php
Log::channel('import')->info("Imported {$count} exercises");
```

Create dedicated import log channel in `config/logging.php`.

### Error Handling
- Wrap each importer in try-catch
- Log errors with context (row number, data)
- Continue vs. abort: configurable

### Performance
- Use chunk() for large CSVs
- Consider using DB::insert() for bulk inserts if needed
- Show progress bars for user feedback

---

## Rollback Plan

If import fails partway through:
1. DB transaction ensures atomic rollback
2. IdMapper cleared automatically
3. User can re-run import command

Consider adding `--resume` flag in future to continue from failure point.

---

## Future Enhancements (Out of Scope)

- [ ] Import routine images
- [ ] Import profile/body measurements
- [ ] Import cardio logs
- [ ] Import notes
- [ ] Handle duplicate detection
- [ ] Incremental imports (update existing records)
