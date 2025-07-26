<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fuente Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="preload" as="image" href="{{ asset('img/logo-pigo-blanco.svg') }}">


    {{-- Estilos y scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Ocultar elementos con x-cloak hasta que Alpine.js esté listo --}}
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-[Poppins] antialiased bg-gray-100">
<x-banner />

{{-- Contenedor principal con control de sidebar --}}
<div
    x-data="{ sidebarAbierta: false, sidebarColapsada: false }"
    class="min-h-screen flex flex-col sm:flex-row">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Contenido principal --}}
    <main class="flex-1 p-4 sm:p-6 bg-gray-100">
        {{-- Título principal --}}
        @if(View::hasSection('title') || View::hasSection('action'))
            <div class="flex justify-between items-center mb-4 sm:mb-6">
                @hasSection('title')
                    <h1 class="text-3xl font-bold">@yield('title')</h1>
                @endif

                @hasSection('action')
                    <div>
                        @yield('action')
                    </div>
                @endif
            </div>
        @endif

        @hasSection('tabs')
            <div class="mb-2">
                @yield('tabs')
            </div>
@endif

