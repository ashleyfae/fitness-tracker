<?php

namespace App\Http\Requests;

class UpdateRoutineRequest extends StoreRoutineRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $routine = $this->route('routine');

        return $routine && auth()->check() && auth()->user()->can('update', $routine);
    }

    public function rules(): array
    {
        $parentRules = parent::rules();

        return array_merge($parentRules, [
            'exercises'                => ['nullable', 'array'],
            'exercises.*.number_sets'  => ['required', 'integer', 'gt:0', 'lte:200'],
            'exercises.*.rest_seconds' => ['required', 'integer', 'gt:0', 'lte:1000'],
            'exercises.*.sort'         => ['required', 'integer', 'gte:0', 'lte:1000'],
        ]);
    }
}
