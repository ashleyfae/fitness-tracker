<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $routine_id
 * @property Carbon $started_at
 * @property Carbon|null $ended_at
 * @property int|null $duration_seconds
 * @property int $total_exercises
 * @property float $total_kg_lifted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property Routine $routine
 * @property WorkoutExercise[]|Collection $exercises
 *
 * @mixin Builder
 */
class WorkoutSession extends Model
{
    /** @use HasFactory<\Database\Factories\WorkoutSessionFactory> */
    use HasFactory;
    use BelongsToUser;

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'total_exercises' => 'integer',
        'total_kg_lifted' => 'float',
    ];

    protected $fillable = [
        'routine_id',
    ];

    public function routine() : BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function exercises() : HasMany
    {
        return $this->hasMany(WorkoutExercise::class);
    }
}
