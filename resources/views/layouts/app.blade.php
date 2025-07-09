@extends('layouts.base')

@section('base')
    <div class="container">
        <nav id="nav">
            <ul>
                <li><a href="{{ route('workouts.create') }}">Start Workout</a></li>
                <li><a href="{{ route('routines.index') }}">Manage Routines</a></li>
                <li><a href="{{ route('exercises.index') }}">Manage Exercises</a></li>
            </ul>
        </nav>

        @yield('app')
    </div>
@endsection
