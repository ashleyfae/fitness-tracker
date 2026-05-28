<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $exercise_id
 * @property int $sort
 * @property int $target_sets
 * @property float $target_weight_kg
 * @property int $target_reps
 * @property Carbon|null $completed_at
 * @property int|null $completed_in_workout_session_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Exercise $exercise
 * @property WorkoutSession|null $completedInWorkoutSession
 *
 * @mixin Builder
 */
class ExerciseGoal extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'sort',
        'target_sets',
        'target_weight_kg',
        'target_reps',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'target_weight_kg' => 'float',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function completedInWorkoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'completed_in_workout_session_id');
    }
}
