@extends('layouts.page')

@section('header')
    <h1>Exercises</h1>
    <a href="{{ route('exercises.create') }}" class="button">Add Exercise</a>
@endsection

@section('content')
    @if($exercises && $exercises->isNotEmpty())
        <div id="routines">
            @foreach($exercises as $exercise)
                <div class="routine">
                    <h2>
                        <a href="{{ route('exercises.edit', $exercise) }}">{{ $exercise->name }}</a>
                    </h2>

                    <form class="delete-exercise" method="POST" action="{{ route('exercises.destroy', $exercise) }}">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="button danger "
                        >Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    @else
        <p>No exercises yet.</p>
    @endif
@endsection
