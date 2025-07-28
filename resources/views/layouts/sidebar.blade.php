{{-- Botón hamburguesa (fijo arriba) --}}
<div class="sm:hidden flex items-center justify-between px-4 py-3 bg-[#0D374B] text-white fixed top-0 left-0 right-0 z-50">
    <span class="font-bold">PIGO</span>
    <button id="btn-sidebar-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
</div>

{{-- Sidebar ocultable: incluye logo, navegación y cierre sesión --}}
<div id="sidebar-container"
     class="fixed top-[48px] left-0 bottom-0 z-40 w-64 sm:relative sm:top-0 sm:z-0 flex flex-col bg-[#0D374B] text-white font-[Poppins] hidden sm:flex">

    {{-- Contenido de sidebar --}}
    <div class="flex-1 overflow-y-auto px-6 py-6 flex flex-col">

        {{-- Logo --}}
        <div class="flex items-center justify-center mb-6">
            <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('img/logo-pigo-blanco.svg'))) }}"
                 alt="PIGO" class="h-24 w-auto" />
        </div>

        {{-- Navegación --}}
        <nav class="space-y-2 text-sm">
            <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <x-icons.home /> Inicio
            </a>
            <a href="/whatsapp" class="sidebar-link {{ request()->is('whatsapp') ? 'active' : '' }}">
                <x-icons.message-square /> Whatsapp
            </a>
            <a href="/clientes" class="sidebar-link {{ request()->is('clientes') ? 'active' : '' }}">
                <x-icons.user /> Clientes
            </a>
            <a href="/pedidos" class="sidebar-link {{ request()->is('pedidos') ? 'active' : '' }}">
                <x-icons.file-text /> Pedidos
            </a>
            <a href="/servicios" class="sidebar-link {{ request()->is('servicios') ? 'active' : '' }}">
                <x-icons.check-circle /> Servicios
            </a>
            <a href="/inventario" class="sidebar-link {{ request()->is('inventario') ? 'active' : '' }}">
                <x-icons.boxes /> Inventario
            </a>
            <a href="/reportes" class="sidebar-link {{ request()->is('reportes') ? 'active' : '' }}">
                <x-icons.bar-chart /> Reportes
            </a>
            <a href="/historial" class="sidebar-link {{ request()->is('historial') ? 'active' : '' }}">
                <x-icons.history /> Historial
            </a>
            <a href="/configuracion" class="sidebar-link {{ request()->is('configuracion') ? 'active' : '' }}">
                <x-icons.settings /> Configuración
            </a>
        </nav>

        {{-- Cerrar sesión --}}
        <div class="mt-auto pt-6 border-t border-white/20">
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
    </div>
</div>
