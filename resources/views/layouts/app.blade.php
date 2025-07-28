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
    <script>
        // Este script se ejecuta inmediatamente para evitar el parpadeo (FOUC)
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.body.classList.add('sidebar-colapsada');
        }
    </script>
<x-banner />

{{-- Contenedor responsive: sidebar arriba en móviles, al lado en pantallas grandes --}}
<div class="min-h-screen flex flex-col sm:flex-row overflow-visible relative z-0 pr-10">


    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Contenido principal --}}
    <main class="flex-1 p-4 sm:p-6 pt-14 sm:pt-6 bg-gray-100">


    {{-- Título principal --}}
        @if(View::hasSection('title') || View::hasSection('action'))
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 sm:gap-0 mb-4 sm:mb-6">
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


        {{ $slot }}
    </main>


</div>

@stack('modals')
@livewireScripts
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleMobile = document.getElementById('btn-sidebar-toggle');
        const sidebarContainer = document.getElementById('sidebar-container');
        const toggleDesktop = document.getElementById('btn-toggle-sidebar');
        const body = document.body;

        // --- LÓGICA DEL SIDEBAR EN ESCRITORIO CON LOCALSTORAGE ---

        // 1. Al cargar la página, revisa el estado guardado en localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            body.classList.add('sidebar-colapsada');
        }

        // 2. Al hacer clic en el botón de escritorio, alterna la clase y guarda el estado
        toggleDesktop?.addEventListener('click', () => {
            body.classList.toggle('sidebar-colapsada');
            // Guarda la preferencia en localStorage
            if (body.classList.contains('sidebar-colapsada')) {
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                localStorage.removeItem('sidebarCollapsed');
            }
        });


        // --- LÓGICA DEL SIDEBAR EN MÓVIL (SIN CAMBIOS) ---
        toggleMobile?.addEventListener('click', () => {
            if (sidebarContainer.classList.contains('hidden')) {
                sidebarContainer.classList.remove('hidden');
                sidebarContainer.classList.add('block');
            } else {
                sidebarContainer.classList.remove('block');
                sidebarContainer.classList.add('hidden');
            }
        });
    });
</script>



@include('components.toast')
</body>
</html>
