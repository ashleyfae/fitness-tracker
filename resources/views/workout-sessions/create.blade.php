@extends('layouts.page')

@section('title', 'Start Workout')

@section('header')
    <h1>Start Workout</h1>
@endsection

@section('content')
    @if($routines && $routines->isNotEmpty())
        <form id="create-workout" method="POST" action="{{ route('workouts.store') }}">
            @csrf

            @foreach($routines as $routine)
                <div class="workout-routine box">
                    <label for="routine-{{ $routine->id }}">{{ $routine->name }}</label>
                    <input id="routine-{{ $routine->id }}" type="radio" name="routine_id" value="{{ $routine->id }}">
                </div>
            @endforeach

            <div id="create-workout-submit" class="hidden">
                <button type="submit">Start Workout</button>
            </div>
        </form>
    @else
        <p>No routines available.</p>
    @endif
@endsection
