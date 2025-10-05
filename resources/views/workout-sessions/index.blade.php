<?php /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\WorkoutSession> $sessions */ ?>
@extends('layouts.page')

@section('title', 'Workout Sessions')

@section('header')
    <h1>Workout Sessions</h1>
@endsection

@section('content')
    <div id="workout-sessions">
        @foreach($sessions as $session)
            <div class="workout-session box">
                <h2>{{ $session->routine?->name ?? 'Unknown Workout' }} (ID: {{ $session->id }})</h2>
                <p>
                    <strong>Started:</strong> {{ $session->started_at->format('Y-m-d H:i') }} |
                    <strong>Ended:</strong> {{ $session->ended_at?->format('Y-m-d H:i') ?? 'N/A' }} |
                    <strong>Duration:</strong> {{ $session->duration_seconds ? gmdate('H:i:s', $session->duration_seconds) : 'N/A' }}
                </p>

                @foreach($session->exercises as $workoutExercise)
                    <div class="workout-session-exercises">
                        <strong>{{ $workoutExercise->exercise->name }}</strong>
                        <ul>
                            @foreach($workoutExercise->sets as $set)
                                <li>{{ $set->weight_kg }}kg × {{ $set->number_reps }} reps</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="pagination">
        {{ $sessions->links() }}
    </div>
@endsection
