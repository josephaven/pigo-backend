{{-- Logo --}}
<div>
    <div class="flex items-center justify-center mb-10">
        <img src="{{ asset('img/logo-pigo-blanco.svg') }}" alt="PIGO" class="h-32 w-auto" />
    </div>

    {{-- Navegación --}}
    <nav class="space-y-2 text-sm">
        <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <x-icons.home />
            Inicio
        </a>
        <a href="/whatsapp" class="sidebar-link {{ request()->is('whatsapp') ? 'active' : '' }}">
            <x-icons.message-square />
            Whatsapp
        </a>
        <a href="/clientes" class="sidebar-link {{ request()->is('clientes') ? 'active' : '' }}">
            <x-icons.user />
            Clientes
        </a>
        <a href="/pedidos" class="sidebar-link {{ request()->is('pedidos') ? 'active' : '' }}">
            <x-icons.file-text />
            Pedidos
        </a>
        <a href="/servicios" class="sidebar-link {{ request()->is('servicios') ? 'active' : '' }}">
            <x-icons.check-circle />
            Servicios
        </a>
        <a href="/inventario" class="sidebar-link {{ request()->is('inventario') ? 'active' : '' }}">
            <x-icons.boxes />
            Inventario
        </a>
        <a href="/reportes" class="sidebar-link {{ request()->is('reportes') ? 'active' : '' }}">
            <x-icons.bar-chart />
            Reportes
        </a>
        <a href="/historial" class="sidebar-link {{ request()->is('historial') ? 'active' : '' }}">
            <x-icons.history />
            Historial
        </a>
        <a href="/configuracion" class="sidebar-link {{ request()->is('configuracion') ? 'active' : '' }}">
            <x-icons.settings />
            Configuración
        </a>
    </nav>
</div>

{{-- Cerrar sesión --}}
<div class="mt-8">
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="sidebar-link logout">
        <x-icons.log-out />
        Cerrar sesión
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>
