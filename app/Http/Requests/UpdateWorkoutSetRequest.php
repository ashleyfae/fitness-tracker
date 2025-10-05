<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user owns the workout set
        return $this->user()->can('update', $this->route('workoutSet'));
    }

    public function rules(): array
    {
        return [
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
        ];
    }
}
