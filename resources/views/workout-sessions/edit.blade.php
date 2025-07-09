<?php /** @var \App\Models\WorkoutSession $workoutSession */ ?>
@extends('layouts.page')

@section('title', 'Workout Session')

@section('header')
    <h1>Workout: {{ $workoutSession->routine->name }}</h1>
@endsection

@section('content')
    <div id="workout-session">
        @foreach($workoutSession->exercises as $exercise)
            <div class="workout-session--exercise">
                <div class="workout-session--exercise--header">
                    <h2>{{ $exercise->name }}</h2>
                </div>

                <div class="workout-session--exercise--body">

                </div>
            </div>
        @endforeach
    </div>
@endsection
