@extends('layouts.base')

@section('base')
    <div class="container">
        <div id="header">
            <nav>
                <ul>
                    <li><a href="{{ route('workouts.create') }}" @class(['active' => request()->routeIs('workouts.create')])>Start</a></li>
                    <li><a href="{{ route('routines.index') }}" @class(['active' => request()->routeIs('routines.*')])>Routines</a></li>
                    <li><a href="{{ route('exercises.index') }}" @class(['active' => request()->routeIs('exercises.*')])>Exercises</a></li>
                    <li><a href="{{ route('workouts.index') }}" @class(['active' => request()->routeIs('workouts.*') && ! request()->routeIs('workouts.create')])>Workouts</a></li>
                </ul>
            </nav>
        </div>

        @yield('app')
    </div>
@endsection
