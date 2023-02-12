<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\ExerciseController
 */
class ExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @covers \App\Http\Controllers\ExerciseController::store()
     * @dataProvider providerCanStore
     */
    public function testUserCanStore(array $postData, int $expectedResponseCode): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('exercises.store'), $postData);

        $response->assertStatus($expectedResponseCode);

        if ($expectedResponseCode === 201) {
            $this->assertDatabaseHas(Exercise::class, $postData);
        } else {
            $this->assertDatabaseMissing(Exercise::class, $postData);
        }
    }

    /** @see testUserCanStore */
    public function providerCanStore() : \Generator
    {
        yield 'name and description is 201' => [
            'postData' => [
                'name' => 'Exercise',
                'description' => 'Description',
            ],
            'expectedResponseCode' => 201,
        ];

        yield 'missing name is 422' => [
            'postData' => [
                'description' => 'Description',
            ],
            'expectedResponseCode' => 422,
        ];
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::store()
     */
    public function testGuestCannotStore(): void
    {
        $response = $this->postJson(route('exercises.store'), ['name' => 'Exercise']);

        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::update()
     */
    public function testUserCanUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Exercise $exercise */
        $exercise = Exercise::factory()->for($user)->create(['name' => 'Exercise 1']);

        $response = $this->actingAs($user)->putJson(route('exercises.update', $exercise), ['name' => 'Exercise 2']);

        $response->assertStatus(200);

        $exercise->refresh();

        $this->assertSame('Exercise 2', $exercise->name);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::update()
     */
    public function testUserCannotUpdateOtherUsersExercise(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        /** @var Exercise $exercise */
        $exercise = Exercise::factory()->for($user1)->create();

        $response = $this->actingAs($user2)->putJson(route('exercises.update', $exercise), ['name' => 'Exercise 2']);

        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::update()
     */
    public function testGuestCannotUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Exercise $exercise */
        $exercise = Exercise::factory()->for($user)->create();

        $response = $this->putJson(route('exercises.update', $exercise), ['name' => 'Exercise 2']);

        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::destroy()
     */
    public function testUserCanDestroy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Exercise $exercise */
        $exercise = Exercise::factory()->for($user)->create(['name' => 'Exercise 1']);

        $response = $this->actingAs($user)->deleteJson(route('exercises.destroy', $exercise));

        $response->assertStatus(200);

        $this->assertModelMissing($exercise);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::destroy()
     */
    public function testUserCannotDestroyOtherUsersExercise(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        /** @var Exercise $exercise */
        $exercise = Exercise::factory()->for($user1)->create();

        $response = $this->actingAs($user2)->deleteJson(route('exercises.destroy', $exercise));

        $response->assertStatus(403);

        $this->assertModelExists($exercise);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::destroy()
     */
    public function testGuestCannotDestroy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Exercise $exercise */
        $exercise = Exercise::factory()->for($user)->create();

        $response = $this->putJson(route('exercises.destroy', $exercise));

        $response->assertStatus(403);

        $this->assertModelExists($exercise);
    }
}
