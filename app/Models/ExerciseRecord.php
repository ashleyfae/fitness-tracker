<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $exercise_id
 * @property float|null $best_weight_kg Actual heaviest weight lifted
 * @property float|null $estimated_1rm_kg Calculated 1-rep max (Epley formula)
 * @property Carbon $achieved_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Exercise $exercise
 *
 * @mixin Builder
 */
class ExerciseRecord extends Model
{
    use BelongsToUser;

    protected $casts = [
        'best_weight_kg' => 'float',
        'estimated_1rm_kg' => 'float',
        'achieved_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'exercise_id',
        'best_weight_kg',
        'estimated_1rm_kg',
        'achieved_at',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
