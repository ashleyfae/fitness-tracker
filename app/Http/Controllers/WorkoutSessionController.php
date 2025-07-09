<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkoutSessionRequest;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;

class WorkoutSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('workout-sessions.create', [
            'routines' => $request->user()->routines()->withCount('exercises')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkoutSessionRequest $request)
    {
        $workoutSession = $request->user()->workoutSessions()->create($request->validated());

        return redirect()->route('workouts.edit', $workoutSession);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkoutSession $workoutSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkoutSession $workoutSession)
    {
        $workoutSession->load([
            'exercises',
            'exercises.exercise',
            'exercises.sets',
        ]);

        return view('workout-sessions.edit', [
            'workoutSession' => $workoutSession,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkoutSession $workoutSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkoutSession $workoutSession)
    {
        //
    }
}
