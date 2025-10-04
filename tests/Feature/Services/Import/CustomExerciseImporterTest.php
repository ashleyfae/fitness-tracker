<?php

namespace Tests\Feature\Services\Import;

use App\Models\Exercise;
use App\Models\User;
use App\Services\Import\IdMapper;
use App\Services\Import\Importers\CustomExerciseImporter;
use App\Services\Import\TimestampConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomExerciseImporterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $testCsvPath;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
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
    public function it_imports_custom_exercises(): void
    {
        $this->createTestCsv([
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","A shrug exercise"',
            '2,123,"2024-01-02",4,"Dumbbell Row","A row exercise"',
        ]);

        $importer = new CustomExerciseImporter(
            $this->user,
            new IdMapper,
            new TimestampConverter,
            $this->testCsvPath
        );

        $importer->import();

        $this->assertEquals(2, $importer->getImportedCount());
        $this->assertEmpty($importer->getErrors());

        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'name' => 'Barbell Shrug',
            'description' => 'A shrug exercise',
        ]);

        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'name' => 'Dumbbell Row',
            'description' => 'A row exercise',
        ]);
    }

    /** @test */
    public function it_handles_exercises_without_description(): void
    {
        $this->createTestCsv([
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Bench Press",',
        ]);

        $importer = new CustomExerciseImporter(
            $this->user,
            new IdMapper,
            new TimestampConverter,
            $this->testCsvPath
        );

        $importer->import();

        $this->assertEquals(1, $importer->getImportedCount());

        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'name' => 'Bench Press',
            'description' => null,
        ]);
    }

    /** @test */
    public function it_updates_existing_exercises(): void
    {
        // Create an existing exercise
        $this->user->exercises()->create([
            'name' => 'Barbell Shrug',
            'description' => 'Original description',
        ]);

        $this->createTestCsv([
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","Updated description"',
        ]);

        $importer = new CustomExerciseImporter(
            $this->user,
            new IdMapper,
            new TimestampConverter,
            $this->testCsvPath
        );

        $importer->import();

        $this->assertEquals(1, $importer->getImportedCount());
        $this->assertEquals(1, Exercise::count()); // Only one exercise should exist

        $this->assertDatabaseHas('exercises', [
            'user_id' => $this->user->id,
            'name' => 'Barbell Shrug',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function it_handles_empty_csv(): void
    {
        $this->createTestCsv([
            'row_id,USERID,TIMESTAMP,rating,name,description',
        ]);

        $importer = new CustomExerciseImporter(
            $this->user,
            new IdMapper,
            new TimestampConverter,
            $this->testCsvPath
        );

        $importer->import();

        $this->assertEquals(0, $importer->getImportedCount());
        $this->assertEquals(0, Exercise::count());
    }

    /** @test */
    public function it_throws_exception_if_csv_file_not_found(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CSV file not found');

        new CustomExerciseImporter(
            $this->user,
            new IdMapper,
            new TimestampConverter,
            'nonexistent.csv'
        );
    }

    /** @test */
    public function it_imports_exercises_for_correct_user(): void
    {
        $otherUser = User::factory()->create();

        $this->createTestCsv([
            'row_id,USERID,TIMESTAMP,rating,name,description',
            '1,123,"2024-01-01",5,"Barbell Shrug","A shrug exercise"',
        ]);

        $importer = new CustomExerciseImporter(
            $this->user,
            new IdMapper,
            new TimestampConverter,
            $this->testCsvPath
        );

        $importer->import();

        $this->assertEquals(1, $this->user->exercises()->count());
        $this->assertEquals(0, $otherUser->exercises()->count());
    }

    private function createTestCsv(array $lines): void
    {
        $this->testCsvPath = $this->testDir.'/custom-exercises.csv';
        File::put($this->testCsvPath, implode("\n", $lines));
    }
}
