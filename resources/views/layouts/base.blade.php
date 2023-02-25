<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.pieces._head')
<body>
<div id="app">
    @yield('base')
</div>
@yield('footer')
</body>
</html>
