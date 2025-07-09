@extends('layouts.page')

@section('header')
    <div class="flex align-center gap-4">
        <h1>Exercises</h1>
        <a href="{{ route('exercises.create') }}" class="button">Add Exercise</a>
    </div>
@endsection

@section('content')
    <table id="exercises">
        <thead>
        <tr>
            <th>Name</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @if($exercises && $exercises->isNotEmpty())
            @foreach($exercises as $exercise)
                <tr class="exercise">
                    <td>
                        <a href="{{ route('exercises.edit', $exercise) }}">{{ $exercise->name }}</a>
                    </td>
                    <td>
                        <form
                            class="delete-exercise"
                            method="POST"
                            action="{{ route('exercises.destroy', $exercise) }}"
                            data-message="Are you sure you want to delete this exercise?"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2">No exercises yet.</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection
