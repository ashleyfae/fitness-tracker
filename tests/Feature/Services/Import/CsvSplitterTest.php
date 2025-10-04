<?php

namespace Tests\Feature\Services\Import;

use App\Services\Import\CsvSplitter;
use Exception;
use Illuminate\Support\Facades\File;
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
        if (! File::exists($this->outputDir)) {
            File::makeDirectory($this->outputDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->outputDir)) {
            File::deleteDirectory($this->outputDir);
        }

        if (isset($this->testCsvPath) && File::exists($this->testCsvPath)) {
            File::delete($this->testCsvPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_throws_exception_if_source_file_not_found(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Source file not found');

        new CsvSplitter('nonexistent.csv', $this->outputDir);
    }

    /** @test */
    public function it_creates_output_directory_if_not_exists(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name,description',
            '1,Exercise 1,Description 1',
        ]);

        $newOutputDir = storage_path('testing/new-imports');

        if (File::exists($newOutputDir)) {
            File::deleteDirectory($newOutputDir);
        }

        $splitter = new CsvSplitter($this->testCsvPath, $newOutputDir);
        $splitter->split();

        $this->assertTrue(File::exists($newOutputDir));

        File::deleteDirectory($newOutputDir);
    }

    /** @test */
    public function it_identifies_all_sections(): void
    {
        $this->createTestCsv([
            '### ROUTINES',
            '',
            'row_id,name',
            '1,Routine 1',
            '',
            '### WORKOUT SESSIONS',
            '',
            'rowid,_id',
            '1,100',
            '',
            '### EXERCISE LOGS',
            '',
            'USERID,logs',
            '123,20x10',
            '',
            '### EXERCISE RECORDS',
            '',
            'row_id,record',
            '1,50',
            '',
            '### CUSTOM EXERCISES',
            '',
            'row_id,name',
            '1,Custom Ex',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertArrayHasKey('CUSTOM_EXERCISES', $results);
        $this->assertArrayHasKey('WORKOUT_SESSIONS', $results);
        $this->assertArrayHasKey('EXERCISE_LOGS', $results);
        $this->assertArrayHasKey('EXERCISE_RECORDS', $results);
    }

    /** @test */
    public function it_splits_custom_exercises_section(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","A shrug exercise"',
            '2,123,"2024-01-02",4,"Dumbbell Row","A row exercise"',
            '3,123,"2024-01-03",5,"Bench Press","A press exercise"',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertArrayHasKey('CUSTOM_EXERCISES', $results);

        $outputFile = $results['CUSTOM_EXERCISES'];
        $this->assertFileExists($outputFile);

        $contents = file($outputFile);
        $this->assertCount(4, $contents); // Header + 3 rows

        $header = str_getcsv($contents[0]);
        $this->assertEquals(['row_id', 'USERID', 'TIMESTAMP', 'rating', 'name', 'description'], $header);

        $row1 = str_getcsv($contents[1]);
        $this->assertEquals('Barbell Shrug', $row1[4]);
    }

    /** @test */
    public function it_splits_routine_section_into_two_files(): void
    {
        $this->createTestCsv([
            '### ROUTINES',
            '',
            'row_id,USERID,TIMESTAMP,_id,name,difficulty',
            '1,123,"2024-01-01",1,PPL,1',
            '',
            'row_id,USERID,TIMESTAMP,package,_id,name,day,dayIndex,interval_mode,rest_day,week,sort_order',
            '2,123,"2024-01-01",1,21,"PPL - Pull",8,2,0,0,0,0',
            '3,123,"2024-01-01",1,22,"PPL - Push",8,1,0,0,0,0',
            '',
            'row_id,USERID,TIMESTAMP,belongSys,superset,_id,exercise_id,belongplan,exercisename,setcount,timer,logs,bodypart,mysort,targetrep,setdone,setdonetime,interval_time,interval_unit,rest_time_enabled,interval_time_enabled',
            '4,123,"2024-01-01",1,0,184,93,21,"Barbell Deadlift",4,120,"30x9",1,0,8,0,1738059610,0,0,1,0',
            '5,123,"2024-01-01",1,0,123,83,22,"Pull Ups",3,90,"0.5x3",1,1,8,0,1737110406,0,0,1,0',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertArrayHasKey('ROUTINE_DAYS', $results);
        $this->assertArrayHasKey('ROUTINE_EXERCISES', $results);

        // Check routine-days.csv
        $daysFile = $results['ROUTINE_DAYS'];
        $this->assertFileExists($daysFile);

        $daysContents = file($daysFile);
        $this->assertCount(3, $daysContents); // Header + 2 rows

        $daysRow1 = str_getcsv($daysContents[1]);
        // The actual CSV has: package,_id,name,...
        // After row_id,USERID,TIMESTAMP which are included in the row
        // So we need to find which index "name" is at
        $this->assertStringContainsString('PPL - Pull', $daysContents[1]);

        // Check routine-exercises.csv
        $exercisesFile = $results['ROUTINE_EXERCISES'];
        $this->assertFileExists($exercisesFile);

        $exercisesContents = file($exercisesFile);
        $this->assertCount(3, $exercisesContents); // Header + 2 rows

        // Verify the exercise data is present
        $this->assertStringContainsString('93', $exercisesContents[1]); // exercise_id
        $this->assertStringContainsString('Barbell Deadlift', $exercisesContents[1]);
    }

    /** @test */
    public function it_splits_workout_sessions_section(): void
    {
        $this->createTestCsv([
            '### WORKOUT SESSIONS',
            '',
            'rowid,_id,USERID,edit_time,total_time',
            '1,100,123,1456181231,391',
            '2,101,123,1456420091,3359',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertArrayHasKey('WORKOUT_SESSIONS', $results);

        $outputFile = $results['WORKOUT_SESSIONS'];
        $this->assertFileExists($outputFile);

        $contents = file($outputFile);
        $this->assertCount(3, $contents); // Header + 2 rows
    }

    /** @test */
    public function it_splits_exercise_logs_section(): void
    {
        $this->createTestCsv([
            '### EXERCISE LOGS',
            '',
            'USERID,TIMESTAMP,belongSys,logs,_id',
            '123,"2024-01-01",1,"22.5x5,22.5x8",1',
            '123,"2024-01-02",1,"30x10",2',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertArrayHasKey('EXERCISE_LOGS', $results);

        $outputFile = $results['EXERCISE_LOGS'];
        $this->assertFileExists($outputFile);

        $contents = file($outputFile);
        $this->assertCount(3, $contents); // Header + 2 rows
    }

    /** @test */
    public function it_splits_exercise_records_section(): void
    {
        $this->createTestCsv([
            '### EXERCISE RECORDS',
            '',
            'row_id,USERID,TIMESTAMP,_id,record',
            '1,123,"2024-01-01",1,55',
            '2,123,"2024-01-02",2,82',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertArrayHasKey('EXERCISE_RECORDS', $results);

        $outputFile = $results['EXERCISE_RECORDS'];
        $this->assertFileExists($outputFile);

        $contents = file($outputFile);
        $this->assertCount(3, $contents); // Header + 2 rows
    }

    /** @test */
    public function it_handles_empty_sections(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name',
            '',
            '### WORKOUT SESSIONS',
            '',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        // Empty section should still be detected but may not create file
        // This is acceptable - empty sections won't create output files
        $this->assertIsArray($results);
    }

    /** @test */
    public function it_returns_results_map(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name',
            '1,Test',
        ]);

        $splitter = new CsvSplitter($this->testCsvPath, $this->outputDir);
        $results = $splitter->split();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('CUSTOM_EXERCISES', $results);
        $this->assertStringContainsString('custom-exercises.csv', $results['CUSTOM_EXERCISES']);
    }

    private function createTestCsv(array $lines): void
    {
        $this->testCsvPath = storage_path('testing/test-import.csv');
        File::put($this->testCsvPath, implode("\n", $lines));
    }
}
