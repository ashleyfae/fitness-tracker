<?php /** @var \App\Models\WorkoutSession $workoutSession */ ?>
@extends('layouts.page')

@section('title', 'Workout Session')

@section('header')
    <h1>Workout: {{ $workoutSession->routine?->name ?? 'Unknown Workout' }}</h1>
@endsection

@section('content')
    <div id="workout-session">
        @foreach($workoutSession->exercises as $exercise)
            <div class="workout-session--exercise">
                <div class="workout-session--exercise--header">
                    <h2>{{ $exercise->exercise->name }}</h2>
                </div>

                <div class="workout-session--exercise--body">
                    @foreach($exercise->sets as $index => $set)
                        <div class="workout-session--exercise--set">
                            <form>
                                <div>{{ $index }}</div>
                                <div>
                                    <input type="text" name="weight_kg" value="{{ $set->weight_kg }}">
                                    <span>kg</span>
                                </div>
                                <div>
                                    <input type="text" name="reps" value="{{ $set->number_reps }}">
                                    <span>reps</span>
                                </div>
                                <div>{{ $set->rest }}</div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection
