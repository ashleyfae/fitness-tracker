<?php

namespace Tests\Feature\Console\Commands\Import;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SplitCsvCommandTest extends TestCase
{
    private string $testCsvPath;

    private string $outputDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->outputDir = storage_path('testing/feature-imports');

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
    public function it_requires_file_argument(): void
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $this->artisan('import:split-csv');
    }

    /** @test */
    public function it_validates_source_file_exists(): void
    {
        $this->artisan('import:split-csv', ['file' => 'nonexistent.csv'])
            ->expectsOutput('Error: Source file not found at nonexistent.csv')
            ->assertFailed();
    }

    /** @test */
    public function it_validates_file_is_csv(): void
    {
        $txtFile = storage_path('testing/test.txt');
        File::put($txtFile, 'test content');

        $this->artisan('import:split-csv', ['file' => $txtFile])
            ->expectsOutput('Error: Source file must be a CSV file')
            ->assertFailed();

        File::delete($txtFile);
    }

    /** @test */
    public function it_splits_csv_successfully(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name,description',
            '1,Exercise 1,Description 1',
            '2,Exercise 2,Description 2',
            '',
            '### WORKOUT SESSIONS',
            '',
            'rowid,_id,total_time',
            '1,100,391',
        ]);

        $this->artisan('import:split-csv', [
            'file' => $this->testCsvPath,
            '--output-dir' => $this->outputDir,
        ])
            ->expectsOutput('✓ CSV split completed successfully!')
            ->assertSuccessful();

        // Verify output files exist
        $this->assertFileExists($this->outputDir.'/custom-exercises.csv');
        $this->assertFileExists($this->outputDir.'/workout-sessions.csv');
    }

    /** @test */
    public function it_displays_results_table(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name',
            '1,Exercise 1',
            '2,Exercise 2',
            '3,Exercise 3',
        ]);

        $this->artisan('import:split-csv', [
            'file' => $this->testCsvPath,
            '--output-dir' => $this->outputDir,
        ])
            ->expectsTable(
                ['Section', 'Output File', 'Rows'],
                [
                    ['CUSTOM_EXERCISES', 'custom-exercises.csv', '3'],
                ]
            )
            ->assertSuccessful();
    }

    /** @test */
    public function it_displays_info_about_splitting(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name',
            '1,Test',
        ]);

        $this->artisan('import:split-csv', [
            'file' => $this->testCsvPath,
            '--output-dir' => $this->outputDir,
        ])
            ->expectsOutput("Splitting CSV: {$this->testCsvPath}")
            ->expectsOutput("Output directory: {$this->outputDir}")
            ->assertSuccessful();
    }

    /** @test */
    public function it_handles_errors_gracefully(): void
    {
        // Create a CSV that will cause an error (no sections)
        $this->createTestCsv([
            'just,some,data',
            '1,2,3',
        ]);

        $this->artisan('import:split-csv', [
            'file' => $this->testCsvPath,
            '--output-dir' => $this->outputDir,
        ])
            ->assertSuccessful(); // Empty results are OK, command should still succeed
    }

    /** @test */
    public function it_uses_default_output_directory(): void
    {
        $this->createTestCsv([
            '### CUSTOM EXERCISES',
            '',
            'row_id,name',
            '1,Test',
        ]);

        // Don't specify --output-dir, should use default
        $this->artisan('import:split-csv', [
            'file' => $this->testCsvPath,
        ])
            ->expectsOutput('Output directory: resources/imports')
            ->assertSuccessful();

        // Clean up default location
        if (File::exists(resource_path('imports/custom-exercises.csv'))) {
            File::delete(resource_path('imports/custom-exercises.csv'));
        }
    }

    private function createTestCsv(array $lines): void
    {
        $this->testCsvPath = storage_path('testing/test-import.csv');
        File::put($this->testCsvPath, implode("\n", $lines));
    }
}
