@extends('layouts.app')

@section('app')
    <h1>Routines</h1>
    <a href="{{ route('routines.create') }}" class="button">Add Routine</a>

    @if($routines && $routines->isNotEmpty())
        <div id="routines">
            @foreach($routines as $routine)
                <div class="routine">
                    <h2>
                        <a href="{{ route('routines.edit', $routine) }}">{{ $routine->name }}</a>
                    </h2>
                </div>
            @endforeach
        </div>
    @else
        <p>No routines yet.</p>
    @endif
@endsection
