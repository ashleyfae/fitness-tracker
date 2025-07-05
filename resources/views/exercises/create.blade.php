@extends('layouts.page')

@section('title', 'Create Exercise')

@section('header')
    <h1>Create Exercise</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('exercises.store') }}" enctype="multipart/form-data">
        @csrf

        @include('exercises._form', ['exercise' => $exercise])

        <p>
            <button
                type="submit"
                class="success"
            >Create</button>
        </p>
    </form>
@endsection
