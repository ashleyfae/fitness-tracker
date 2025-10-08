<?php

/**
 * ListExercises.php
 *
 * @copyright Copyright (c) 2023, Ashley Gibson
 * @license   MIT
 */

namespace App\Actions\Exercises;

use App\Http\Requests\ListExercisesRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class ListExercises
{
    public function fromRequest(ListExercisesRequest $request): Collection|Paginator
    {
        $data = $request->validated();

        return $request->user()
            ->exercises()
            ->withCount(['workoutExercises'])
            ->when(! empty($data['search']), fn (Builder $builder) => $builder->whereLike('name', '%'.$data['search'].'%'))
            ->orderBy('name')
            ->paginate(40);
    }
}
