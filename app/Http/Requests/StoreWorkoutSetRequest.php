<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route model binding policy
    }

    public function rules(): array
    {
        return [
            'weight_kg' => 'required|numeric|min:0',
            'number_reps' => 'required|integer|min:0',
        ];
    }
}
