<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'NeuroZvuk'))</title>
    <meta name="description" content="@yield('description', '')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Open Graph (переопределить через @section('og_title'), ('og_description'), ('og_image'), ('og_type') на странице) --}}
    <meta property="og:title" content="@yield('og_title', config('app.name', 'NeuroZvuk'))">
    <meta property="og:description" content="@yield('og_description', '')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('img/logo.png'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="ru_RU">
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', config('app.name', 'NeuroZvuk'))">
    <meta name="twitter:description" content="@yield('og_description', '')">
    <meta name="twitter:image" content="@yield('og_image', asset('img/logo.png'))">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon/120.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @stack('json-ld')
    @stack('head')
</head>
<body>

@include('layouts.header')

@include('layouts.mobile-menu')

@yield('content')

@include('layouts.footer')

@include('layouts.scripts')
@stack('scripts')
</body>
</html>
