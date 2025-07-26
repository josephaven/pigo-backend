{{-- Botón hamburguesa solo visible en móvil --}}
<div class="sm:hidden flex items-center justify-between px-4 py-3 bg-[#0D374B] text-white">
    <span class="font-bold">PIGO</span>
    <button @click="sidebarAbierta = !sidebarAbierta">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
</div>

{{-- Sidebar --}}
<aside
    x-cloak
    :class="[
        sidebarAbierta ? 'block' : 'hidden sm:block',
        sidebarColapsada ? 'sm:w-20' : 'sm:w-64'
    ]"
    class="bg-[#0D374B] text-white w-full min-h-screen px-4 sm:px-6 py-8 flex flex-col justify-between font-[Poppins] fixed sm:relative z-50 sm:z-0 transition-all duration-300">

    {{-- Logo --}}
    <div>
        <div class="flex items-center justify-between sm:justify-center mb-10">
            <button
                class="hidden sm:block text-white"
                @click="sidebarColapsada = !sidebarColapsada"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5l-7 7 7 7" />
                </svg>
            </button>

            <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('img/logo-pigo-blanco.svg'))) }}"
                 alt="PIGO" class="h-32 w-auto" />

        </div>

        {{-- Navegación --}}
        <nav class="space-y-2 text-sm">
            <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}" title="Inicio">
                <x-icons.home />
                <span x-show="!sidebarColapsada">Inicio</span>
            </a>
            <a href="/whatsapp" class="sidebar-link {{ request()->is('whatsapp') ? 'active' : '' }}" title="Whatsapp">
                <x-icons.message-square />
                <span x-show="!sidebarColapsada">Whatsapp</span>
            </a>
            <a href="/clientes" class="sidebar-link {{ request()->is('clientes') ? 'active' : '' }}" title="Clientes">
                <x-icons.user />
                <span x-show="!sidebarColapsada">Clientes</span>
            </a>
            <a href="/pedidos" class="sidebar-link {{ request()->is('pedidos') ? 'active' : '' }}" title="Pedidos">
                <x-icons.file-text />
                <span x-show="!sidebarColapsada">Pedidos</span>
            </a>
            <a href="/servicios" class="sidebar-link {{ request()->is('servicios') ? 'active' : '' }}" title="Servicios">
                <x-icons.check-circle />
                <span x-show="!sidebarColapsada">Servicios</span>
            </a>
            <a href="/inventario" class="sidebar-link {{ request()->is('inventario') ? 'active' : '' }}" title="Inventario">
                <x-icons.boxes />
                <span x-show="!sidebarColapsada">Inventario</span>
            </a>
            <a href="/reportes" class="sidebar-link {{ request()->is('reportes') ? 'active' : '' }}" title="Reportes">
                <x-icons.bar-chart />
                <span x-show="!sidebarColapsada">Reportes</span>
            </a>
            <a href="/historial" class="sidebar-link {{ request()->is('historial') ? 'active' : '' }}" title="Historial">
                <x-icons.history />
                <span x-show="!sidebarColapsada">Historial</span>
            </a>
            <a href="/configuracion" class="sidebar-link {{ request()->is('configuracion') ? 'active' : '' }}" title="Configuración">
                <x-icons.settings />
                <span x-show="!sidebarColapsada">Configuración</span>
            </a>
        </nav>
    </div>

    {{-- Cerrar sesión --}}
    <div class="mt-8">
        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="sidebar-link logout" title="Cerrar sesión">
            <x-icons.log-out />
            <span x-show="!sidebarColapsada">Cerrar sesión</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</aside>
