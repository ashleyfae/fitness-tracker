<?php

namespace App\Http\Controllers;

use App\Actions\WorkoutSessions\PrepareWorkoutSessionData;
use App\Http\Requests\StoreWorkoutSessionRequest;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;

class WorkoutSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessions = $request->user()
            ->workoutSessions()
            ->with([
                'routine',
                'exercises.exercise',
                'exercises.sets',
            ])
            ->orderByDesc('ended_at')
            ->paginate(20);

        return view('workout-sessions.index', [
            'sessions' => $sessions,
        ]);
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
     * Display the workout session.
     * This is designed to be used after a session has completed and is no longer in-progress.
     */
    public function show(WorkoutSession $workoutSession)
    {
        //
    }

    /**
     * Show the form for editing the workout session.
     * This is the UI to be shown while a session is in-progress and exercises are being completed in real time.
     */
    public function edit(WorkoutSession $workoutSession, PrepareWorkoutSessionData $prepareData)
    {
        $exercises = $prepareData->execute($workoutSession);

        return view('workout-sessions.edit', [
            'workoutSession' => $workoutSession,
            'exercises' => $exercises,
        ]);
    }

    /**
     * Complete the workout session.
     */
    public function complete(WorkoutSession $workoutSession)
    {
        $workoutSession->update(['ended_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('workouts.show', $workoutSession),
            ]);
        }

        return redirect()->route('workouts.show', $workoutSession);
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
