<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workout_session_id
 * @property int $exercise_id
 * @property int $number_sets
 * @property int $rest_seconds
 * @property int $sort
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class WorkoutExercise extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'workout_session_id' => 'integer',
        'exercise_id' => 'integer',
        'number_sets' => 'integer',
        'rest_seconds' => 'integer',
        'sort' => 'integer',
    ];
}
