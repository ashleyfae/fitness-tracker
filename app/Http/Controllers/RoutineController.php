<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Routine::class, 'routine');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRoutineRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoutineRequest $request)
    {
        $routine = $request->user()->routines()->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json($routine->toArray(), 201);
        } else {
            // @TODO
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Http\Response
     */
    public function show(Routine $routine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Http\Response
     */
    public function edit(Routine $routine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRoutineRequest  $request
     * @param  \App\Models\Routine  $routine
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRoutineRequest $request, Routine $routine)
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
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Routine $routine)
    {
        $routine->delete();

        if ($request->expectsJson()) {
            return response()->json(null);
        } else {
            // @TODO
        }
    }
}
