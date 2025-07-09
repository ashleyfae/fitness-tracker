@extends('layouts.page')

@section('title', 'Manage Routines')

@section('header')
    <div class="flex align-center gap-4">
        <h1>Routines</h1>
        <a href="{{ route('routines.create') }}" class="button">Add Routine</a>
    </div>
@endsection

@section('content')
    <table id="routines">
        <thead>
        <tr>
            <th>Name</th>
            <th>Exercises</th>
            <th>Last completed</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @if($routines && $routines->isNotEmpty())
            @foreach($routines as $routine)
                <tr class="routine">
                    <td>
                        <a href="{{ route('routines.edit', $routine) }}">{{ $routine->name }}</a>
                    </td>
                    <td>{{ $routine->exercises_count }}</td>
                    <td>
                        TODO
                    </td>
                    <td>
                        <form
                            class="delete-form"
                            method="POST"
                            action="{{ route('routines.destroy', $routine) }}"
                            data-message="Are you sure you want to delete this routine?"
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
                <td colspan="4">No routines yet.</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection
