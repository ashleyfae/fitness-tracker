@extends('layouts.page')

@section('title', 'Edit Exercise')

@section('header')
    <h1>Edit Exercise "{{ $exercise->name }}"</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('exercises.update', $exercise) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf

        @include('exercises._form', ['exercise' => $exercise])

        <p>
            <button
                type="submit"
                class="success"
            >Update</button>
        </p>
    </form>
@endsection
