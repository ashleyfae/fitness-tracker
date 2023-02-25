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
use Illuminate\Database\Eloquent\Collection;

class ListExercises
{
    public function fromRequest(ListExercisesRequest $request): Collection|Paginator
    {
        $request = $request->validated();

        $exercises = $request->user()
            ->exercises()
            ->orderBy('name')
            ->paginate(40);

        return $exercises;
    }
}
