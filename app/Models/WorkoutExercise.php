<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property WorkoutSession $workoutSession
 * @property Exercise $exercise
 * @property WorkoutSet[]|Collection $sets
 *
 * @mixin Builder
 */
class WorkoutExercise extends Model
{
    public $incrementing = true;

    protected $casts = [
        'workout_session_id' => 'integer',
        'exercise_id' => 'integer',
        'number_sets' => 'integer',
        'rest_seconds' => 'integer',
        'sort' => 'integer',
    ];

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutSet::class);
    }
}
