<?php

namespace App\Http\Controllers;

use App\Actions\Exercises\ListExercises;
use App\Actions\Exercises\StoreExercise;
use App\Http\Requests\ListExercisesRequest;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExerciseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Exercise::class, 'exercise');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ListExercisesRequest $request, ListExercises $listExercises): JsonResponse|View
    {
        $exercises = $listExercises->fromRequest($request);

        if ($request->expectsJson()) {
            return response()->json($exercises->toArray());
        } else {
            return view('exercises.index', ['exercises' => $exercises]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response|View
    {
        return view('exercises.create', ['exercise' => new Exercise]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExerciseRequest $request, StoreExercise $storeExercise): JsonResponse|RedirectResponse
    {
        $exercise = $storeExercise->fromRequest($request);

        if ($request->expectsJson()) {
            return response()->json($exercise->toArray(), 201);
        } else {
            $request->session()->put('success', 'Exercise created');

            return redirect()->route('exercises.index');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Exercise $exercise): JsonResponse|View
    {
        if ($request->wantsJson()) {
            return response()->json($exercise->toArray());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exercise $exercise): JsonResponse|View
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): JsonResponse|RedirectResponse
    {
        $exercise->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json($exercise->toArray());
        } else {
            // @TODO
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Exercise $exercise): JsonResponse|RedirectResponse
    {
        $exercise->delete();

        if ($request->expectsJson()) {
            return response()->json(null);
        } else {
            $request->session()->put('success', 'Exercise deleted');

            return redirect()->route('exercises.index');
        }
    }
}
