<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Routine;
use App\Models\User;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\RoutineController
 */
class RoutineControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @covers \App\Http\Controllers\RoutineController::store()
     * @dataProvider providerCanStore
     */
    public function testUserCanStore(array $postData, int $expectedResponseCode): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('routines.store'), $postData);

        $response->assertStatus($expectedResponseCode);

        if ($expectedResponseCode === 201) {
            $this->assertDatabaseHas(Routine::class, $postData);
        } else {
            $this->assertDatabaseMissing(Routine::class, $postData);
        }
    }

    /** @see testUserCanStore */
    public function providerCanStore() : Generator
    {
        yield 'name is 201' => [
            'postData' => [
                'name' => 'My First Routine',
            ],
            'expectedResponseCode' => 201,
        ];

        yield 'missing name is 422' => [
            'postData' => [],
            'expectedResponseCode' => 422,
        ];
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::store()
     */
    public function testGuestCannotStore(): void
    {
        $response = $this->postJson(route('routines.store'), ['name' => 'Routine']);

        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::update()
     */
    public function testUserCanUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create(['name' => 'Routine 1']);

        $response = $this->actingAs($user)->putJson(route('routines.update', $routine), ['name' => 'Routine 2']);

        $response->assertStatus(200);

        $routine->refresh();

        $this->assertSame('Routine 2', $routine->name);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::update()
     */
    public function testUserCannotUpdateOtherUsersRoutine(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        /** @var Routine $routine */
        $routine = Routine::factory()->for($user1)->create();

        $response = $this->actingAs($user2)->putJson(route('routines.update', $routine), ['name' => 'Routine 2']);

        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::update()
     */
    public function testGuestCannotUpdate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create();

        $response = $this->putJson(route('routines.update', $routine), ['name' => 'Routine 2']);

        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::destroy()
     */
    public function testUserCanDestroy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create(['name' => 'Routine 1']);

        $response = $this->actingAs($user)->deleteJson(route('routines.destroy', $routine));

        $response->assertStatus(200);

        $this->assertModelMissing($routine);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::destroy()
     */
    public function testUserCannotDestroyOtherUsersRoutine(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        /** @var Routine $routine */
        $routine = Routine::factory()->for($user1)->create();

        $response = $this->actingAs($user2)->deleteJson(route('routines.destroy', $routine));

        $response->assertStatus(403);

        $this->assertModelExists($routine);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::destroy()
     */
    public function testGuestCannotDestroy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create();

        $response = $this->putJson(route('routines.destroy', $routine));

        $response->assertStatus(403);

        $this->assertModelExists($routine);
    }
}
