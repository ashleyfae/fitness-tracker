<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user owns the workout exercise (via workout session)
        return $this->user()->can('update', $this->route('workoutExercise'));
    }

    public function rules(): array
    {
        return [
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
        ];
    }
}
