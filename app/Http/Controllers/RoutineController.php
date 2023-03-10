<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Routine::class, 'routine');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) : JsonResponse|View
    {
        $routines = $request->user()->routines()->orderBy('name')->paginate(10);

        if ($request->expectsJson()) {
            return response()->json($routines->toArray());
        } else {
            return view('routines.index', [
                'routines' => $routines,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() : View
    {
        return view('routines.create', ['routine' => new Routine()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoutineRequest $request) : JsonResponse|RedirectResponse
    {
        $routine = $request->user()->routines()->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json($routine->toArray(), 201);
        } else {
            $request->session()->put('success', 'Routine created');

            return redirect()->route('routines.show', $routine);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Routine $routine) : JsonResponse|View
    {
        if ($request->wantsJson()) {
            $routine->load('exercises');

            return response()->json($routine->toArray());
        }

        return view('routines.show', ['routine' => $routine]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Routine $routine) : View
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoutineRequest $request, Routine $routine) : JsonResponse|RedirectResponse
    {
        $routine->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json($routine->toArray());
        } else {
            // @TODO
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Routine $routine) : JsonResponse|RedirectResponse
    {
        $routine->delete();

        if ($request->expectsJson()) {
            return response()->json(null);
        } else {
            $request->session()->put('success', 'Routine deleted');

            return redirect()->route('routines.index');
        }
    }
}
