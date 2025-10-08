<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property ?string $description
 * @property ?string $image_path
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ExerciseRoutine|null $pivot
 *
 * @property Collection<WorkoutExercise>|WorkoutExercise[] $workoutExercises
 * @property int|null $workout_exercises_count
 *
 * @mixin Builder
 */
class Exercise extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
    ];

    public function workoutExercises() : HasMany
    {
        return $this->hasMany(WorkoutExercise::class);
    }
}
