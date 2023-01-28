<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()
            ->create();

        $this->createExercises($user);

        Exercise::factory()
            ->for($user)
            ->create([
                'name' => 'Bench Press',
            ]);

        Exercise::factory()
            ->for($user)
            ->create([
                'name' => 'Push-Up',
            ]);
    }

    protected function createExercises(User $user): void
    {
        $exercises = [
            [
                'name' => 'Bench Press',
            ],
            [
                'name' => 'Push-Up',
            ],
            [
                'name' => 'Dumbbell Arnold Press',
            ],
            [
                'name' => 'Barbell Military Press',
            ],
            [
                'name' => 'Dumbbell Tricep Kickback',
            ],
            [
                'name' => 'Dumbbell Lateral Raise',
            ],
            [
                'name' => 'Dumbbell One-Arm Front Raise',
            ],
            [
                'name' => 'Hanging Leg Raise',
            ],
            [
                'name' => 'Dumbbell External Rotation',
            ],
            [
                'name' => 'Barbell Deadlift',
            ],
            [
                'name' => 'Pull-Up',
            ],
            [
                'name' => 'Barbell Bent-Over Row',
            ],
            [
                'name' => 'Dumbbell One-Arm Row',
            ],
            [
                'name' => 'Dumbbell Hammer Curl',
            ],
            [
                'name' => 'Dumbbell Incline Curl',
            ],
            [
                'name' => 'Barbell Shrug',
            ],
            [
                'name' => 'Plank',
            ],
            [
                'name' => 'Barbell Squat',
            ],
            [
                'name' => 'Bulgarian Split Squat',
            ],
            [
                'name' => 'Front Squat',
            ],
            [
                'name' => 'Barbell Romanian Deadlift',
            ],
            [
                'name' => 'Single-leg Deadlift',
            ],
            [
                'name' => 'Dumbbell Lunge',
            ],
            [
                'name' => 'Barbell Hip Thrust',
            ],
            [
                'name' => 'Barbell Standing Calf Raise',
            ],
        ];

        foreach($exercises as $exerciseArgs) {
            Exercise::factory()->for($user)->create($exerciseArgs);
        }
    }
}
