<?php
/**
 * ListExercises.php
 *
 * @package   fitness-tracker
 * @copyright Copyright (c) 2023, Ashley Gibson
 * @license   MIT
 */

namespace App\Actions\Exercises;

use App\Http\Requests\ListExercisesRequest;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ListExercises
{
    public function fromRequest(ListExercisesRequest $request): Collection|Paginator
    {
        $data = $request->validated();

        $exercises = $request->user()
            ->exercises()
            ->when(! empty($data['search']), fn(\Illuminate\Contracts\Database\Eloquent\Builder $builder) => $builder->whereLike('name', '%'.$data['search'].'%'))
            ->orderBy('name')
            ->paginate(40);

        return $exercises;
    }
}
