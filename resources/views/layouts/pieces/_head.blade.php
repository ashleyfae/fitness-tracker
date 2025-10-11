<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @if(\Illuminate\Support\Facades\Config::get('app.noindex'))
        <meta name="robots" content="noindex">
    @endif

    @if(Route::current()->getName() === 'home')
        <title>Fitness Tracker</title>
        <meta name="description" content="Keep track of the books you read & stay updated with new releases.">
    @else
        <title>@yield('title') | {{ config('app.name', 'Fitness Tracker') }}</title>
    @endif

    @routes

    <!-- Scripts -->
    <script src="{{ mix('assets/js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ mix('assets/css/app.css') }}" rel="stylesheet">

    @yield('head')
</head>
