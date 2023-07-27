<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="nofollow, noindex" />
    <title>Dlabhr</title>
    @vite('resources/css/app.css')
    @if (config('app.env')=='local')
        <link rel="icon" type="image/png" href="{{ asset('img/favicon-32x32.png') }}" />
    @else
        <link rel="icon" type="image/png" href="{{ secure_asset('img/favicon-32x32.png') }}" />
    @endif
</head>

<body class="antialiased">
    <div id="app">
        <router-view></router-view>
    </div>
    @vite('resources/js/app.js')
</body>

</html>