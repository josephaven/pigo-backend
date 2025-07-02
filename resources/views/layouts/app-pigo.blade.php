<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PIGO') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="flex min-h-screen">
        {{-- Barra lateral --}}
        <aside class="w-64 bg-[#174F5C] text-white p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-center mb-6">
                    <img src="{{ asset('img/logo-pigo.png') }}" alt="PIGO Logo" class="w-20 h-20 object-contain">
                </div>

                <nav class="space-y-2 text-sm">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        <i class="fas fa-home mr-2"></i> Inicio
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-comments mr-2"></i> Whatsapp
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-user mr-2"></i> Clientes
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-list-alt mr-2"></i> Pedidos
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-tools mr-2"></i> Servicios
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-box mr-2"></i> Inventario
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-chart-bar mr-2"></i> Reportes
                    </x-nav-link>
                    <x-nav-link href="#" :active="false">
                        <i class="fas fa-history mr-2"></i> Historial
                    </x-nav-link>
                    <x-nav-link href="{{ route('configuracion.empleados') }}" :active="request()->routeIs('configuracion.empleados')">
                        <i class="fas fa-cog mr-2"></i> Configuración
                    </x-nav-link>
                </nav>
            </div>

            <div class="mt-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        {{-- Contenido principal --}}
        <main class="flex-1 p-6 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
