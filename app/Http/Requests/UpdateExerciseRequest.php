<?php

namespace App\Http\Requests;

use App\Models\Exercise;

class UpdateExerciseRequest extends StoreExerciseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $exercise = $this->route('exercise');

        return auth()->check() && $exercise instanceof Exercise && auth()->user()->can('update', $exercise);
    }
}
