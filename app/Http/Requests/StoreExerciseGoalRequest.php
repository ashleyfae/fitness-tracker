<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseGoalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_sets'     => ['required', 'integer', 'min:1'],
            'target_weight_kg' => ['required', 'numeric', 'min:0'],
            'target_reps'     => ['required', 'integer', 'min:1'],
        ];
    }
}
