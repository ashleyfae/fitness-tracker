<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $exercise_id
 * @property int $routine_id
 * @property int $number_sets
 * @property int $rest_seconds
 * @property int $sort
 */
class ExerciseRoutine extends Pivot
{
    //
}
