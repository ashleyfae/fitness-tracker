<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user owns the workout session
        return $this->user()->can('update', $this->route('workoutSession'));
    }

    public function rules(): array
    {
        return [
            'exercise_id' => 'required|exists:exercises,id',
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
            'rest_seconds' => 'required|integer|min:0',
            'sort' => 'required|integer|min:0',
        ];
    }
}
