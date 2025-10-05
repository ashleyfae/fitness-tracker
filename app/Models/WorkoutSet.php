<?php

namespace App\Models;

use App\Observers\WorkoutSetObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $workout_exercise_id
 * @property float $weight_kg
 * @property int $number_reps
 * @property ?Carbon $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property WorkoutExercise $workoutExercise
 *
 * @mixin Builder
 */
#[ObservedBy(WorkoutSetObserver::class)]
class WorkoutSet extends Model
{
    protected $casts = [
        'workout_exercise_id' => 'integer',
        'weight_kg' => 'float',
        'number_reps' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $fillable = [
        'weight_kg',
        'number_reps',
        'completed_at',
    ];

    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }
}
