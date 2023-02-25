@extends('layouts.app')

@section('app')
    <h1>Create Exercise</h1>

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
