<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkoutSession>
 */
class WorkoutSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = Carbon::createFromTimestamp($this->faker->dateTime('-1 day')->getTimestamp());

        return [
            'user_id' => User::factory(),
            'routine_id' => Routine::factory(),
            'started_at' => $startedAt->toDateTimeString(),
            'ended_at' => $startedAt->addMinutes($this->faker->numberBetween(30, 120))->toDateTimeString(),
            'total_exercises' => $this->faker->numberBetween(1, 10),
            'total_kg_lifted' => $this->faker->numberBetween(10, 100),
        ];
    }
}
