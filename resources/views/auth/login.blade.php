@extends('layouts.base')

@section('base')
    <div id="login" class="container">
        <h1>Log in</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <p>
                <label for="email">Email:</label> <br>
                <input
                    type="email"
                    id="email"
                    name="email"
                    autofocus
                >
            </p>
            @error('email')
            <span class="colour--danger">{{ $message }}</span>
            @enderror

            <p>
                <label for="password">Password:</label> <br>
                <input
                    type="password"
                    id="password"
                    name="password"
                >
            </p>
            @error('password')
            <span class="colour--danger">{{ $message }}</span>
            @enderror

            <p>
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    checked
                >
                <label for="remember">Remember me</label>
            </p>

            <p>
                <button type="submit">Login</button>
            </p>
        </form>
    </div>
@endsection
