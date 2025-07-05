@extends('layouts.app')

@section('app')
    <main>
        <header class="page-header">
            @yield('header')
        </header>

        @if(session()->get('success'))
            <div class="notification success">
                {{ session()->get('success') }}
            </div>
        @endif

        @yield('content')
    </main>
@endsection
