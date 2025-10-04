<?php

namespace Tests\Feature\Console\Commands\Import;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportDataCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Test User']);
        $this->testDir = storage_path('testing/imports');

        if (! File::exists($this->testDir)) {
            File::makeDirectory($this->testDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testDir)) {
            File::deleteDirectory($this->testDir);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_fails_if_user_not_found(): void
    {
        $this->artisan('import:data', ['--user' => 999])
            ->expectsOutput('User with ID 999 not found.')
            ->assertFailed();
    }

    /** @test */
    public function it_fails_if_input_directory_not_found(): void
    {
        $this->artisan('import:data', [
            '--user' => $this->user->id,
            '--input-dir' => 'nonexistent/directory',
        ])
            ->expectsOutput('Input directory not found: nonexistent/directory')
            ->assertFailed();
    }

    /** @test */
    public function it_fails_if_required_csv_files_missing(): void
    {
        $this->artisan('import:data', [
            '--user' => $this->user->id,
            '--input-dir' => $this->testDir,
        ])
            ->expectsOutputToContain('Required CSV file not found')
            ->assertFailed();
    }

    /** @test */
    public function it_imports_custom_exercises(): void
    {
        $this->createTestCsv('custom-exercises.csv', [
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","A shrug exercise"',
            '2,123,"2024-01-02",4,"Dumbbell Row","A row exercise"',
        ]);

        $this->artisan('import:data', [
            '--user' => $this->user->id,
            '--input-dir' => $this->testDir,
        ])
            ->expectsConfirmation("This will import data for user {$this->user->name} (ID: {$this->user->id}). Continue?", 'yes')
            ->expectsOutput('Starting import...')
            ->expectsOutput('Importing custom exercises...')
            ->expectsOutput('  ✓ Imported 2 custom exercises')
            ->expectsOutput('Import completed successfully!')
            ->assertSuccessful();

        $this->assertEquals(2, Exercise::count());
        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'name' => 'Barbell Shrug',
        ]);
    }

    /** @test */
    public function it_supports_dry_run_mode(): void
    {
        $this->createTestCsv('custom-exercises.csv', [
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","A shrug exercise"',
        ]);

        $this->artisan('import:data', [
            '--user' => $this->user->id,
            '--input-dir' => $this->testDir,
            '--dry-run' => true,
        ])
            ->expectsOutput('Running in dry-run mode...')
            ->expectsOutput('Dry-run completed successfully. No data was persisted.')
            ->assertSuccessful();

        // No data should be persisted
        $this->assertEquals(0, Exercise::count());
    }

    /** @test */
    public function it_allows_cancelling_import(): void
    {
        $this->createTestCsv('custom-exercises.csv', [
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","A shrug exercise"',
        ]);

        $this->artisan('import:data', [
            '--user' => $this->user->id,
            '--input-dir' => $this->testDir,
        ])
            ->expectsConfirmation("This will import data for user {$this->user->name} (ID: {$this->user->id}). Continue?", 'no')
            ->expectsOutput('Import cancelled.')
            ->assertSuccessful();

        $this->assertEquals(0, Exercise::count());
    }

    /** @test */
    public function it_displays_import_summary(): void
    {
        $this->createTestCsv('custom-exercises.csv', [
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Exercise 1","Description"',
        ]);

        $this->artisan('import:data', [
            '--user' => $this->user->id,
            '--input-dir' => $this->testDir,
        ])
            ->expectsConfirmation("This will import data for user {$this->user->name} (ID: {$this->user->id}). Continue?", 'yes')
            ->expectsOutput('Import Summary:')
            ->expectsTable(['Metric', 'Count'], [
                ['Total Records Imported', 1],
                ['Total Errors', 0],
            ])
            ->assertSuccessful();
    }

    /** @test */
    public function it_uses_default_user_id_1(): void
    {
        // Create user with ID 1
        User::factory()->create(['id' => 1, 'name' => 'Default User']);

        $this->createTestCsv('custom-exercises.csv', [
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Exercise 1","Description"',
        ]);

        $this->artisan('import:data', [
            '--input-dir' => $this->testDir,
        ])
            ->expectsConfirmation('This will import data for user Default User (ID: 1). Continue?', 'yes')
            ->assertSuccessful();

        $this->assertEquals(1, Exercise::where('user_id', 1)->count());
    }

    private function createTestCsv(string $filename, array $lines): void
    {
        $path = $this->testDir.'/'.$filename;
        File::put($path, implode("\n", $lines));
    }
}
