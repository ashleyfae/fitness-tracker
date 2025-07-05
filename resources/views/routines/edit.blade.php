@extends('layouts.app')

@section('app')
    <h1>Routine: {{ $routine->name }}</h1>

    <form method="POST" action="{{ route('routines.update', $routine) }}">
        @csrf

        <div
            id="exercise-list"
            data-get="{{ route('routines.show', $routine) }}"
        >
            <p>Loading exercises...</p>
        </div>

        <button
            type="button"
            class="modal-trigger"
            data-target="add-exercise-modal"
        >Add Exercise</button>
    </form>

    <x-elements.modal id="add-exercise-modal">
        <h3>Add Exercise</h3>

        <form method="POST">
            <x-features.search-exercises />
        </form>
    </x-elements.modal>
@endsection
