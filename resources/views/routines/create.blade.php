@extends('layouts.app')

@section('app')
    <h1>Create Routine</h1>

    <form method="POST" action="{{ route('routines.store') }}">
        @csrf

        @include('routines._form', ['routine' => $routine])

        <p>
            <button
                type="submit"
                class="success"
            >Create</button>
        </p>
    </form>
@endsection
