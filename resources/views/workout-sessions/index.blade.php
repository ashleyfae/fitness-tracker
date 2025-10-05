<?php /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\WorkoutSession> $sessions */ ?>
@extends('layouts.page')

@section('title', 'Workout Sessions')

@section('header')
    <h1>Workout Sessions</h1>
@endsection

@section('content')
    @foreach($sessions as $session)
        <div style="border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem;">
            <h2>{{ $session->routine?->name ?? 'Unknown Workout' }}</h2>
            <p>
                <strong>Started:</strong> {{ $session->started_at->format('Y-m-d H:i') }} |
                <strong>Ended:</strong> {{ $session->ended_at?->format('Y-m-d H:i') ?? 'N/A' }} |
                <strong>Duration:</strong> {{ $session->duration_seconds ? gmdate('H:i:s', $session->duration_seconds) : 'N/A' }}
            </p>

            @foreach($session->exercises as $workoutExercise)
                <div style="margin-left: 1rem; margin-bottom: 0.5rem;">
                    <strong>{{ $workoutExercise->exercise->name }}</strong>
                    <ul style="margin: 0; padding-left: 2rem;">
                        @foreach($workoutExercise->sets as $set)
                            <li>{{ $set->weight_kg }}kg × {{ $set->number_reps }} reps</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endforeach

    {{ $sessions->links() }}
@endsection
