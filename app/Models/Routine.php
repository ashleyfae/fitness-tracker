<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property Exercise[]|Collection $exercises
 *
 * @mixin Builder
 */
class Routine extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'name',
    ];

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class)
            ->using(ExerciseRoutine::class)
            ->withPivot(['number_sets', 'rest_seconds', 'sort'])
            ->orderByPivot('sort');
    }
}
