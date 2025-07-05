<?php
/**
 * UpdateRoutine.php
 *
 * @package   fitness-tracker
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace App\Actions\Routines;

use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;

class UpdateRoutine
{
    public function execute(Routine $routine, UpdateRoutineRequest $request) : Routine
    {
        $data = $request->validated();

        $routine->update($data);
        $routine->exercises()->sync($data['exercises'] ?? []);

        return $routine;
    }
}
