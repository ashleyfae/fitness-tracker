@extends('layouts.page')

@section('title', 'Create Routine')

@section('header')
    <h1>Create Routine</h1>
@endsection

@section('content')
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
