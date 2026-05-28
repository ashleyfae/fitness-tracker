@extends('layouts.page')

@section('title', 'Edit Exercise')

@section('header')
    <h1>Edit Exercise "{{ $exercise->name }}"</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('exercises.update', $exercise) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf

        @include('exercises._form', ['exercise' => $exercise])

        <p>
            <button
                type="submit"
                class="success"
            >Update</button>
        </p>
    </form>

    <section class="exercise-goals-section">
        <h2>Progression Goals</h2>

        <div
            id="exercise-goals"
            data-store-url="{{ route('exercises.goals.store', $exercise) }}"
        >
            @forelse($goals as $goal)
                <div class="exercise-goal"
                     data-id="{{ $goal->id }}"
                     data-sets="{{ $goal->target_sets }}"
                     data-weight="{{ $goal->target_weight_kg }}"
                     data-reps="{{ $goal->target_reps }}">
                    <div class="exercise-goal--summary">
                        {{ $goal->target_sets }} sets &times; {{ $goal->target_weight_kg }}kg &times; {{ $goal->target_reps }} reps
                    </div>
                    <div class="exercise-goal--actions">
                        <button type="button" class="small exercise-goal--move-up" aria-label="Move up">▲</button>
                        <button type="button" class="small exercise-goal--move-down" aria-label="Move down">▼</button>
                        <button type="button" class="small exercise-goal--edit">Edit</button>
                        <button type="button" class="small danger exercise-goal--delete">Delete</button>
                    </div>
                </div>
            @empty
                <p class="notification" id="no-goals-message">No goals yet.</p>
            @endforelse
        </div>

        <button type="button" class="mt-2" id="add-goal-btn">Add Goal</button>
    </section>

    <x-elements.modal id="goal-modal">
        <h3 id="goal-modal-title">Add Goal</h3>
        <form id="goal-form">
            <div class="field">
                <label for="goal-sets">Sets</label>
                <input type="number" id="goal-sets" name="target_sets" min="1" required>
            </div>
            <div class="field">
                <label for="goal-weight">Weight (kg)</label>
                <input type="number" id="goal-weight" name="target_weight_kg" min="0" step="0.5" required>
            </div>
            <div class="field">
                <label for="goal-reps">Reps per set</label>
                <input type="number" id="goal-reps" name="target_reps" min="1" required>
            </div>
            <button type="submit" class="success">Save Goal</button>
        </form>
    </x-elements.modal>
@endsection
