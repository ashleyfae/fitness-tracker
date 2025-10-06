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
         data-csrf="{{ csrf_token() }}"
         data-total-exercises="{{ $exercises->count() }}">

        @foreach($exercises as $exerciseData)
            <div class="exercise"
                 data-exercise-id="{{ $exerciseData->exercise->id }}"
                 data-workout-exercise-id="{{ $exerciseData->workoutExerciseId }}"
                 data-expected-sets="{{ $exerciseData->expectedSets }}"
                 data-rest-seconds="{{ $exerciseData->restSeconds }}"
                 data-sort="{{ $exerciseData->sort }}">

                <div class="exercise-header">
                    <h2>{{ $exerciseData->exercise->name }}</h2>
                </div>

                <div class="sets-container">
                    @for($i = 1; $i <= $exerciseData->maxSets(); $i++)
                        @php
                            $set = $exerciseData->actualSets->get($i - 1);
                            $previousSet = $exerciseData->previousSets?->get($i - 1);
                            $isCompleted = $set !== null;
                            $isNext = !$isCompleted && $exerciseData->actualSets->count() === $i - 1;

                            $classes = ['set'];
                            if ($isCompleted) {
                                $classes[] = 'set--complete';
                            } else {
                                $classes[] = 'set--incomplete';
                                if ($isNext) {
                                    $classes[] = 'set--next';
                                }
                            }
                        @endphp

                        <div class="{{ implode(' ', $classes) }}" data-set-index="{{ $i }}" @if($set) data-set-id="{{ $set->id }}" @endif>
                            <div class="set--number">Set {{ $i }}</div>

                            <div class="set--fields">
                                @if($set)
                                    {{-- Existing set (editable) --}}
                                    <div class="set--field-group">
                                        <input type="number"
                                               class="set-weight"
                                               data-set-id="{{ $set->id }}"
                                               value="{{ $set->weight_kg }}"
                                               step="0.5"
                                               placeholder="Weight (kg)">
                                        <span>kg</span>
                                    </div>
                                    <div class="set--field-group">
                                        <input type="number"
                                               class="set-reps"
                                               data-set-id="{{ $set->id }}"
                                               value="{{ $set->number_reps }}"
                                               placeholder="Reps">
                                        <span>reps</span>
                                    </div>
                                    <button class="save-set" data-set-id="{{ $set->id }}" aria-label="Save set">&#10003;</button>
                                    <button class="delete-set" data-set-id="{{ $set->id }}" aria-label="Delete set">&times;</button>
                                @else
                                    {{-- Empty set (show previous values as defaults) --}}
                                    <div class="set--field-group">
                                        <input type="number"
                                               class="set-weight"
                                               value="{{ $previousSet?->weight_kg ?? '' }}"
                                               step="0.5"
                                               placeholder="Weight (kg)">
                                        <span>kg</span>
                                    </div>
                                    <div class="set--field-group">
                                        <input type="number"
                                               class="set-reps"
                                               value="{{ $previousSet?->number_reps ?? '' }}"
                                               placeholder="Reps">
                                        <span>reps</span>
                                    </div>
                                    <button class="add-set" aria-label="Add set">&#10003;</button>
                                    <button class="dummy-delete-set">&times;</button> {{-- here to avoid shifted layout compared to existing set above --}}
                                @endif
                            </div>
                        </div>
                    @endfor

                    <div class="add-extra-set-wrap">
                        {{-- Button to add additional sets beyond expected --}}
                        <button class="add-extra-set">+ Add Another Set</button>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="workout-actions">
            <button id="complete-workout">Complete Workout</button>
        </div>
    </div>

    {{-- Rest Timer Modal --}}
    <div id="rest-timer-modal">
        <h3>Rest:</h3>
        <div class="rest-timer-content">
            <div id="rest-timer-display">0:00</div>
            <button id="skip-rest">Skip Rest</button>
        </div>
    </div>
@endsection

@section('head')
    <script src="{{ mix('assets/js/workout-session.js') }}"></script>
@endsection
