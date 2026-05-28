<?php

namespace App\Http\Controllers;

use App\Actions\ExerciseGoals\StoreExerciseGoal;
use App\Actions\ExerciseGoals\UpdateExerciseGoal;
use App\Http\Requests\StoreExerciseGoalRequest;
use App\Http\Requests\UpdateExerciseGoalRequest;
use App\Models\Exercise;
use App\Models\ExerciseGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseGoalController extends Controller
{
    public function store(StoreExerciseGoalRequest $request, Exercise $exercise, StoreExerciseGoal $action): JsonResponse
    {
        $this->authorize('create', [ExerciseGoal::class, $exercise]);

        $goal = $action->execute($exercise, $request->validated());

        return response()->json($goal->toArray(), 201);
    }

    public function update(UpdateExerciseGoalRequest $request, Exercise $exercise, ExerciseGoal $goal, UpdateExerciseGoal $action): JsonResponse
    {
        $this->authorize('update', $goal);

        $goal = $action->execute($goal, $request->validated());

        return response()->json($goal->toArray());
    }

    public function destroy(Request $request, Exercise $exercise, ExerciseGoal $goal): JsonResponse
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return response()->json(null);
    }

    public function reorder(Request $request, Exercise $exercise, ExerciseGoal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        $direction = $request->input('direction');

        $neighbor = $direction === 'up'
            ? $exercise->goals()->whereNull('completed_at')->where('sort', '<', $goal->sort)->orderByDesc('sort')->first()
            : $exercise->goals()->whereNull('completed_at')->where('sort', '>', $goal->sort)->orderBy('sort')->first();

        if ($neighbor) {
            [$goal->sort, $neighbor->sort] = [$neighbor->sort, $goal->sort];
            $goal->save();
            $neighbor->save();
        }

        return response()->json(null);
    }
}
