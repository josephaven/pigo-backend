<!DOCTYPE html>
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

    {{-- Estilos y scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Ocultar elementos con x-cloak hasta que Alpine.js esté listo --}}
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-[Poppins] antialiased bg-gray-100">
<x-banner />

{{-- Contenedor responsive: sidebar arriba en móviles, al lado en pantallas grandes --}}
<div class="min-h-screen flex flex-col sm:flex-row">

    {{-- Sidebar --}}
    <aside class="w-full sm:w-64 bg-[#003844] text-white">
        @include('layouts.sidebar')
    </aside>

    {{-- Contenido principal --}}
    <main class="flex-1 p-4 sm:p-6 bg-gray-100">
        {{ $slot }}
    </main>
</div>

@stack('modals')
@livewireScripts
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@include('components.toast')
</body>
</html>
