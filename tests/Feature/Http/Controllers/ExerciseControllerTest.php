<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\ExerciseController;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ExerciseController::class)]
class ExerciseControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @covers \App\Http\Controllers\ExerciseController::index()
     */
    public function testUserCanList(): void
    {
        $user = User::factory()->create();
        $exercises = Exercise::factory()->for($user)->count(10)->create();
        $otherExercise = Exercise::factory()->count(2)->create();

        $response = $this->actingAs($user)->getJson(route('exercises.index'));

        $response->assertStatus(200);

        $body = json_decode($response->content(), true);

        $this->assertSame(1, $body['current_page']);
        $this->assertSame(10, $body['total']);
        $this->assertCount(10, $body['data']);
    }

    /**
     * @covers \App\Http\Controllers\ExerciseController::store()
     * @dataProvider providerCanStore
     */
    public function testUserCanStore(array $postData, int $expectedResponseCode): void
    {
        Storage::fake();

        $user = User::factory()->create();

        // add an image
        $file = UploadedFile::fake()->image('exercise.jpg');
        $postData['image'] = $file;

        $response = $this->actingAs($user)->postJson(route('exercises.store'), $postData);

        $response->assertStatus($expectedResponseCode);

        $dataToCheck = $postData;
        unset($dataToCheck['image']);

        if ($expectedResponseCode === 201) {
            $dataToCheck['image_path'] = "exercises/{$file->hashName()}";

            $this->assertDatabaseHas(Exercise::class, $dataToCheck);
            Storage::assertExists("exercises/{$file->hashName()}");
        } else {
            $this->assertDatabaseMissing(Exercise::class, $dataToCheck);
            Storage::assertMissing("exercises/{$file->hashName()}");
        }
    }

    /** @see testUserCanStore */
    public static function providerCanStore() : \Generator
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

        $response->assertUnauthorized();
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

        $response->assertForbidden();
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

        $response->assertUnauthorized();
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

        $response->assertForbidden();

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

        $response->assertUnauthorized();

        $this->assertModelExists($exercise);
    }
}
