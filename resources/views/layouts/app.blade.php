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
    <link rel="preload" as="image" href="{{ asset('img/logo-pigo-blanco.svg') }}">

    {{-- Estilos y scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Ocultar elementos con x-cloak hasta que Alpine.js esté listo --}}
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="font-[Poppins] antialiased bg-gray-100">

<x-banner />

<div x-data="{ sidebarAbierta: false }" class="min-h-screen flex">

    {{-- Botón hamburguesa (solo en móvil) --}}
    <div class="sm:hidden fixed top-0 left-0 w-full z-50 bg-[#003844] flex items-center justify-between px-4 py-3 text-white">
        <span class="font-bold">PIGO</span>
        <button @click="sidebarAbierta = !sidebarAbierta">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- Fondo oscuro al abrir sidebar (móvil) --}}
    <div x-show="sidebarAbierta"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 sm:hidden"
         @click="sidebarAbierta = false"
         x-cloak></div>

    {{-- SIDEBAR: fijo en móvil, relativo en desktop --}}
    <aside
        :class="sidebarAbierta ? 'translate-x-0' : '-translate-x-full'"
        class="fixed sm:relative z-50 sm:z-0 bg-[#003844] text-white w-64 h-full px-6 py-8 flex flex-col justify-between font-[Poppins] transition-transform duration-300 ease-in-out sm:translate-x-0"
    >
        @include('layouts.sidebar')
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="flex-1 p-4 sm:p-6 bg-gray-100 mt-16 sm:mt-0">
        @if(View::hasSection('title') || View::hasSection('action'))
            <div class="flex justify-between items-center mb-4 sm:mb-6">
                @hasSection('title')
                    <h1 class="text-3xl font-bold">@yield('title')</h1>
                @endif

                @hasSection('action')
                    <div>@yield('action')</div>
                @endif
            </div>
        @endif

        @hasSection('tabs')
            <div class="mb-2">@yield('tabs')</div>
        @endif

        {{ $slot }}
    </main>
</div>

@stack('modals')
@livewireScripts
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@include('components.toast')

</body>

