<?php

namespace Tests\Feature\Actions\Routines;

use App\Actions\Routines\UpdateRoutine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UpdateRoutine::class)]
class UpdateRoutineTest extends TestCase
{
    public function testCanNormalizeExercises() : void
    {
        $exercises = [
            3 => [
                'number_sets' => 1,
                'rest_seconds' => 60,
                'sort' => 5,
            ],
            1 => [
                'number_sets' => 1,
                'rest_seconds' => 60,
                'sort' => 0,
            ],
            2 => [
                'number_sets' => 1,
                'rest_seconds' => 60,
                'sort' => 1,
            ],
            4 => [
                'number_sets' => 1,
                'rest_seconds' => 60,
                'sort' => 5,
            ],
        ];

        $this->assertSame(
            [
                1 => [
                    'number_sets' => 1,
                    'rest_seconds' => 60,
                    'sort' => 0,
                ],
                2 => [
                    'number_sets' => 1,
                    'rest_seconds' => 60,
                    'sort' => 1,
                ],
                3 => [
                    'number_sets' => 1,
                    'rest_seconds' => 60,
                    'sort' => 2,
                ],
                4 => [
                    'number_sets' => 1,
                    'rest_seconds' => 60,
                    'sort' => 3,
                ],
            ],
            $this->invokeInaccessibleMethod(new UpdateRoutine(), 'normalizeExercises', $exercises)
        );
    }
}
