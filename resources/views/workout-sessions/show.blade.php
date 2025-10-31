<?php /** @var \App\Models\WorkoutSession $session */ ?>
@extends('layouts.page')

@section('title', 'Workout Session')

@section('header')
    <h1>{{ $session->routine?->name ?? 'Unknown Workout' }}</h1>
    <p>{{ $session->started_at->format('l, F j, Y') }}</p>
@endsection

@section('content')
    <div id="workout-session-show">
        <div class="workout-session box">
            <div class="workout-session-summary">
                <p>
                    <strong>Started:</strong> {{ $session->started_at->format('g:i A') }} |
                    <strong>Ended:</strong> {{ $session->ended_at?->format('g:i A') ?? 'N/A' }} |
                    <strong>Duration:</strong> {{ $session->duration_seconds ? gmdate('H:i:s', $session->duration_seconds) : 'N/A' }}
                </p>
                <p>
                    <strong>Total Exercises:</strong> {{ $session->total_exercises }} |
                    <strong>Total Weight Lifted:</strong> {{ number_format($session->total_kg_lifted, 1) }} kg
                </p>
            </div>

            @if($session->exercises->isEmpty())
                <p class="no-exercises">No exercises completed in this workout session.</p>
            @else
                @foreach($session->exercises as $workoutExercise)
                    <div class="workout-session-exercises">
                        <h2 class="mb-0">{{ $workoutExercise->exercise->name }}</h2>
                        <p class="exercise-summary mt-0">
                            {{ $workoutExercise->number_sets }} {{ Str::plural('set', $workoutExercise->number_sets) }}
                        </p>

                        <ul class="sets-list">
                            @foreach($workoutExercise->sets as $set)
                                <li>
                                    {{ $set->weight_kg }}kg × {{ $set->number_reps }} reps
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="workout-actions mt-4">
            <a href="{{ route('workouts.index') }}" class="button">Back to All Workouts</a>
        </div>
    </div>
@endsection
