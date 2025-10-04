<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\IdMapper;
use Exception;
use PHPUnit\Framework\TestCase;

class IdMapperTest extends TestCase
{
    private IdMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new IdMapper;
    }

    /** @test */
    public function it_maps_exercise_name(): void
    {
        $this->mapper->mapExerciseName(123, 'Barbell Squat');

        $this->assertTrue($this->mapper->hasExerciseName(123));
        $this->assertEquals('Barbell Squat', $this->mapper->getExerciseName(123));
    }

    /** @test */
    public function it_throws_exception_when_exercise_name_not_found(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Exercise name mapping not found for old ID: 999');

        $this->mapper->getExerciseName(999);
    }

    /** @test */
    public function it_returns_false_when_exercise_name_not_mapped(): void
    {
        $this->assertFalse($this->mapper->hasExerciseName(999));
    }

    /** @test */
    public function it_maps_routine(): void
    {
        $this->mapper->mapRoutine(21, 100);

        $this->assertTrue($this->mapper->hasRoutine(21));
        $this->assertEquals(100, $this->mapper->getRoutineId(21));
    }

    /** @test */
    public function it_throws_exception_when_routine_not_found(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Routine mapping not found for old ID: 999');

        $this->mapper->getRoutineId(999);
    }

    /** @test */
    public function it_returns_false_when_routine_not_mapped(): void
    {
        $this->assertFalse($this->mapper->hasRoutine(999));
    }

    /** @test */
    public function it_maps_session(): void
    {
        $this->mapper->mapSession(500, 200);

        $this->assertTrue($this->mapper->hasSession(500));
        $this->assertEquals(200, $this->mapper->getSessionId(500));
    }

    /** @test */
    public function it_throws_exception_when_session_not_found(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Session mapping not found for old ID: 999');

        $this->mapper->getSessionId(999);
    }

    /** @test */
    public function it_returns_false_when_session_not_mapped(): void
    {
        $this->assertFalse($this->mapper->hasSession(999));
    }

    /** @test */
    public function it_clears_all_mappings(): void
    {
        $this->mapper->mapExerciseName(123, 'Squat');
        $this->mapper->mapRoutine(21, 100);
        $this->mapper->mapSession(500, 200);

        $this->mapper->clear();

        $this->assertFalse($this->mapper->hasExerciseName(123));
        $this->assertFalse($this->mapper->hasRoutine(21));
        $this->assertFalse($this->mapper->hasSession(500));
    }

    /** @test */
    public function it_returns_mapping_counts(): void
    {
        $this->mapper->mapExerciseName(1, 'Exercise 1');
        $this->mapper->mapExerciseName(2, 'Exercise 2');
        $this->mapper->mapRoutine(21, 100);
        $this->mapper->mapSession(500, 200);
        $this->mapper->mapSession(501, 201);
        $this->mapper->mapSession(502, 202);

        $counts = $this->mapper->getCounts();

        $this->assertEquals(2, $counts['exercise_names']);
        $this->assertEquals(1, $counts['routines']);
        $this->assertEquals(3, $counts['sessions']);
    }

    /** @test */
    public function it_handles_multiple_exercise_names(): void
    {
        $this->mapper->mapExerciseName(1, 'Squat');
        $this->mapper->mapExerciseName(2, 'Deadlift');
        $this->mapper->mapExerciseName(3, 'Bench Press');

        $this->assertEquals('Squat', $this->mapper->getExerciseName(1));
        $this->assertEquals('Deadlift', $this->mapper->getExerciseName(2));
        $this->assertEquals('Bench Press', $this->mapper->getExerciseName(3));
    }

    /** @test */
    public function it_overwrites_existing_mappings(): void
    {
        $this->mapper->mapExerciseName(123, 'Original Name');
        $this->mapper->mapExerciseName(123, 'Updated Name');

        $this->assertEquals('Updated Name', $this->mapper->getExerciseName(123));
    }
}
