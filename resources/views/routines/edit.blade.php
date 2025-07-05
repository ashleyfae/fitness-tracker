@extends('layouts.page')

@section('title', 'Edit Routine')

@section('header')
    <h1>Edit Routine: {{ $routine->name }}</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('routines.update', $routine) }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ $routine->name }}">
        </div>

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

        <button type="submit">Save</button>
    </form>

    <x-elements.modal id="add-exercise-modal">
        <h3>Add Exercise</h3>

        <form method="POST">
            <x-features.search-exercises />
        </form>
    </x-elements.modal>
@endsection
