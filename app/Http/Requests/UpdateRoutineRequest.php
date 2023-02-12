<?php

namespace App\Http\Requests;

class UpdateRoutineRequest extends StoreRoutineRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        $routine = $this->route('routine');

        return $routine && auth()->check() && auth()->user()->can('update', $routine);
    }
}
