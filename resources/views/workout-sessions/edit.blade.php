<?php
/** @var \App\Models\WorkoutSession $workoutSession */
/** @var \Illuminate\Support\Collection<\App\DataTransferObjects\WorkoutExerciseData> $exercises */
?>
@extends('layouts.page')

@section('title', 'Workout Session')

@section('header')
    <h1>Workout: {{ $workoutSession->routine?->name ?? 'Unknown Workout' }}</h1>
    <p>Started: {{ $workoutSession->started_at->format('g:i A') }}</p>
@endsection

@section('content')
    <div id="workout-session"
         data-session-id="{{ $workoutSession->id }}"
         data-csrf="{{ csrf_token() }}">

        @foreach($exercises as $exerciseData)
            <div class="exercise"
                 data-exercise-id="{{ $exerciseData->exercise->id }}"
                 data-workout-exercise-id="{{ $exerciseData->workoutExerciseId }}"
                 data-expected-sets="{{ $exerciseData->expectedSets }}"
                 data-rest-seconds="{{ $exerciseData->restSeconds }}"
                 data-sort="{{ $exerciseData->sort }}">

                <div class="exercise-header">
                    <h2>{{ $exerciseData->exercise->name }}</h2>
                    @if($exerciseData->fromRoutine)
                        <span class="badge">From routine</span>
                    @else
                        <span class="badge">Added manually</span>
                    @endif
                </div>

                <div class="sets-container">
                    @for($i = 1; $i <= $exerciseData->maxSets(); $i++)
                        @php
                            $set = $exerciseData->actualSets->get($i - 1);
                        @endphp

                        <div class="set" data-set-index="{{ $i }}" @if($set) data-set-id="{{ $set->id }}" @endif>
                            <label>Set {{ $i }}</label>

                            <div class="set--fields">
                                @if($set)
                                    {{-- Existing set (editable) --}}
                                    <input type="number"
                                           class="set-weight"
                                           data-set-id="{{ $set->id }}"
                                           value="{{ $set->weight_kg }}"
                                           step="0.5"
                                           placeholder="Weight (kg)">
                                    <input type="number"
                                           class="set-reps"
                                           data-set-id="{{ $set->id }}"
                                           value="{{ $set->number_reps }}"
                                           placeholder="Reps">
                                    <button class="save-set" data-set-id="{{ $set->id }}">Save</button>
                                    <button class="delete-set" data-set-id="{{ $set->id }}">Delete</button>
                                @else
                                    {{-- Empty set from routine (ready to add) --}}
                                    <input type="number"
                                           class="set-weight"
                                           step="0.5"
                                           placeholder="Weight (kg)">
                                    <input type="number"
                                           class="set-reps"
                                           placeholder="Reps">
                                    <button class="add-set">Save Set</button>
                                @endif
                            </div>
                        </div>
                    @endfor

                    {{-- Button to add additional sets beyond expected --}}
                    <button class="add-extra-set">+ Add Another Set</button>
                </div>
            </div>
        @endforeach

        <div class="workout-actions">
            <button id="complete-workout">Complete Workout</button>
        </div>
    </div>
@endsection

@section('head')
    <script src="{{ mix('assets/js/workout-session.js') }}"></script>
@endsection
