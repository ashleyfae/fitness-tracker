<?php
/**
 * StoreExercise.php
 *
 * @package   fitness-tracker
 * @copyright Copyright (c) 2023, Ashley Gibson
 * @license   MIT
 */

namespace App\Actions\Exercises;

use App\Http\Requests\StoreExerciseRequest;
use App\Models\Exercise;

class StoreExercise
{
    public function fromRequest(StoreExerciseRequest $request): Exercise
    {
        $args = $request->validated();

        if ($request->file('image')) {
            $args['image_path'] = $request->file('image')->storePublicly('exercises');
        }

        /** @var Exercise $exercise */
        $exercise = $request->user()->exercises()->create($args);

        return $exercise;
    }
}
