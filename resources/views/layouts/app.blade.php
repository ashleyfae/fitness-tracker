@extends('layouts.base')

@section('base')
    <div class="container">
        <div id="header">
            <nav>
                <ul>
                    <li><a href="{{ route('workouts.create') }}">Start Workout</a></li>
                    <li><a href="{{ route('routines.index') }}">Manage Routines</a></li>
                    <li><a href="{{ route('exercises.index') }}">Manage Exercises</a></li>
                </ul>
            </nav>
        </div>

        @yield('app')
    </div>
@endsection
