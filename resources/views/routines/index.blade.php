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
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="3">No routines yet.</td>
            </tr>
        @endif
        </tbody>
    </table>
@endsection
