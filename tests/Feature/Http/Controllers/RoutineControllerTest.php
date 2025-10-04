<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Controllers\RoutineController;
use App\Models\Exercise;
use App\Models\Routine;
use App\Models\User;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(RoutineController::class)]
class RoutineControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @covers \App\Http\Controllers\RoutineController::store()
     *
     * @dataProvider providerCanStore
     */
    public function test_user_can_store(array $postData, int $expectedResponseCode): void
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
    public static function providerCanStore(): Generator
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
    public function test_guest_cannot_store(): void
    {
        $response = $this->postJson(route('routines.store'), ['name' => 'Routine']);

        $response->assertUnauthorized();
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::update()
     */
    public function test_user_can_update(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create(['name' => 'Routine 1']);

        /** @var Exercise $exercise1 */
        $exercise1 = Exercise::factory()->create();
        /** @var Exercise $exercise2 */
        $exercise2 = Exercise::factory()->create();

        $this->assertEmpty($routine->exercises);

        $response = $this->actingAs($user)
            ->putJson(
                route('routines.update', $routine),
                [
                    'name' => 'Routine 2',
                    'exercises' => [
                        $exercise1->id => [
                            'number_sets' => 1,
                            'rest_seconds' => 90,
                            'sort' => 1,
                        ],
                        $exercise2->id => [
                            'number_sets' => 2,
                            'rest_seconds' => 120,
                            'sort' => 2,
                        ],
                    ],
                ]
            );

        $response->assertStatus(200);

        $routine->refresh();

        $this->assertSame('Routine 2', $routine->name);
        $this->assertCount(2, $routine->exercises);

        $exerciseRoutine1 = $routine->exercises->firstWhere('id', $exercise1->id);
        $this->assertSame(1, $exerciseRoutine1->pivot->number_sets);
        $this->assertSame(90, $exerciseRoutine1->pivot->rest_seconds);
        $this->assertSame(0, $exerciseRoutine1->pivot->sort);

        $exerciseRoutine2 = $routine->exercises->firstWhere('id', $exercise2->id);
        $this->assertSame(2, $exerciseRoutine2->pivot->number_sets);
        $this->assertSame(120, $exerciseRoutine2->pivot->rest_seconds);
        $this->assertSame(1, $exerciseRoutine2->pivot->sort);

        /** now update again to delete an exercise */
        $response = $this->actingAs($user)
            ->putJson(
                route('routines.update', $routine),
                [
                    'name' => 'Routine 2',
                    'exercises' => [
                        $exercise1->id => [
                            'number_sets' => 1,
                            'rest_seconds' => 90,
                            'sort' => 1,
                        ],
                    ],
                ]
            );

        $response->assertStatus(200);

        $routine->refresh();

        $this->assertCount(1, $routine->exercises);

        $exerciseRoutine1 = $routine->exercises->firstWhere('id', $exercise1->id);
        $this->assertSame(1, $exerciseRoutine1->pivot->number_sets);
        $this->assertSame(90, $exerciseRoutine1->pivot->rest_seconds);
        $this->assertSame(0, $exerciseRoutine1->pivot->sort);
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::update()
     */
    public function test_user_cannot_update_other_users_routine(): void
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
    public function test_guest_cannot_update(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create();

        $response = $this->putJson(route('routines.update', $routine), ['name' => 'Routine 2']);

        $response->assertUnauthorized();
    }

    /**
     * @covers \App\Http\Controllers\RoutineController::destroy()
     */
    public function test_user_can_destroy(): void
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
    public function test_user_cannot_destroy_other_users_routine(): void
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
    public function test_guest_cannot_destroy(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Routine $routine */
        $routine = Routine::factory()->for($user)->create();

        $response = $this->putJson(route('routines.destroy', $routine));

        $response->assertUnauthorized();

        $this->assertModelExists($routine);
    }
}
