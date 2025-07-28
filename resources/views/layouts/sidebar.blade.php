{{-- Botón hamburguesa (visible en móvil, fijo arriba) --}}
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

{{-- Sidebar contenedor (ocultable en móvil y colapsable en escritorio) --}}
<div id="sidebar-container"
     class="fixed top-[48px] left-0 bottom-0 z-40 sm:relative sm:top-0 sm:z-0 flex flex-col transition-all duration-300 bg-[#0D374B] text-white font-[Poppins] w-64 sm:w-64 overflow-visible sm:flex">

    {{-- Contenido interno --}}
    <div class="flex-1 px-4 py-4 flex flex-col">


        {{-- Botón de colapsar (solo visible en sm) --}}
        <button id="btn-toggle-sidebar"
                class="hidden sm:flex items-center justify-center w-8 h-8 mx-auto mb-6 text-white hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
            </svg>
        </button>

        {{-- Logo --}}
        <div id="logo-wrapper" class="flex items-center justify-center mb-6 transition-all duration-300">
            <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('img/logo-pigo-blanco.svg'))) }}"
                 alt="PIGO" class="h-24 w-auto sidebar-logo" />
        </div>

        {{-- Navegación --}}
        <nav class="space-y-2 text-sm">
            @php
                $links = [
                    ['href' => '/dashboard', 'icon' => 'home', 'text' => 'Inicio'],
                    ['href' => '/whatsapp', 'icon' => 'message-square', 'text' => 'Whatsapp'],
                    ['href' => '/clientes', 'icon' => 'user', 'text' => 'Clientes'],
                    ['href' => '/pedidos', 'icon' => 'file-text', 'text' => 'Pedidos'],
                    ['href' => '/servicios', 'icon' => 'check-circle', 'text' => 'Servicios'],
                    ['href' => '/inventario', 'icon' => 'boxes', 'text' => 'Inventario'],
                    ['href' => '/reportes', 'icon' => 'bar-chart', 'text' => 'Reportes'],
                    ['href' => '/historial', 'icon' => 'history', 'text' => 'Historial'],
                    ['href' => '/configuracion', 'icon' => 'settings', 'text' => 'Configuración'],
                ];
            @endphp

            @foreach ($links as $link)
                <a href="{{ $link['href'] }}"
                   class="sidebar-link flex items-center gap-3 group relative {{ request()->is(ltrim($link['href'], '/')) ? 'active' : '' }}">
                    <x-dynamic-component :component="'icons.' . $link['icon']" />
                    <span class="sidebar-label">{{ $link['text'] }}</span>
                    <span class="sidebar-tooltip absolute left-full top-1/2 -translate-y-1/2 px-2 py-1 text-xs rounded bg-black text-white whitespace-nowrap z-[999]">
                        {{ $link['text'] }}
                    </span>

                </a>
            @endforeach
        </nav>

        {{-- Cerrar sesión --}}
        <div id="logout-wrapper" class="mt-auto pt-6 border-t border-white/20">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="sidebar-link flex items-center gap-3 group relative logout">
                <x-icons.log-out />
                <span class="sidebar-label">Cerrar sesión</span>
                <span class="sidebar-tooltip absolute left-full top-1/2 -translate-y-1/2 px-2 py-1 text-xs rounded bg-black text-white whitespace-nowrap z-[999]">
                    Cerrar sesión
                </span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</div>
