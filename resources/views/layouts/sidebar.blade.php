<div class="relative flex">



{{-- Fondo oscuro al abrir sidebar en móvil --}}
    <div x-show="sidebarAbierta"
         class="fixed inset-0 bg-black bg-opacity-40 z-40 sm:hidden"
         @click="sidebarAbierta = false"></div>

    {{-- Botón hamburguesa SOLO en móvil --}}
    <div class="sm:hidden fixed top-4 left-4 z-50">
        <button @click="sidebarAbierta = !sidebarAbierta" class="bg-white p-2 rounded shadow">
            <svg class="h-6 w-6 text-gray-800" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- SIDEBAR --}}
    <aside
        :class="[
            sidebarAbierta ? 'translate-x-0 w-64' : '-translate-x-full',
            sidebarColapsada ? 'sm:w-20' : 'sm:w-64',
            'sm:translate-x-0'
        ]"
        class="fixed sm:relative top-0 left-0 min-h-screen
               bg-[#0D374B] text-white px-4 py-6 flex flex-col font-[Poppins] z-50
               transition-transform duration-300 ease-in-out transform overflow-visible"
    >

        {{-- Contenido interno del sidebar --}}
        <div class="flex flex-col justify-between h-full">
            <div>
                <div class="flex flex-col" :class="sidebarColapsada ? 'gap-y-8' : 'gap-y-4'">

                    <div class="hidden sm:flex w-full" :class="sidebarColapsada ? 'justify-center' : 'justify-end'">
                        <button @click="sidebarColapsada = !sidebarColapsada"
                                class="text-white hover:text-gray-300 transition p-1">
                            <svg x-show="!sidebarColapsada" xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                            </svg>
                            <svg x-show="sidebarColapsada" xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-col items-center space-y-2">
                        <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('img/logo-pigo-blanco.svg'))) }}"
                             alt="PIGO"
                             :class="sidebarColapsada ? 'h-12' : 'h-24'"
                             class="transition-all duration-300 mx-auto" />
                        <span x-show="!sidebarColapsada" class="transition-opacity"></span>
                    </div>

                    <nav class="space-y-2 text-sm">

                        {{-- === INICIO === --}}
                        <a href="/dashboard" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.home />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Inicio</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Inicio
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === WHATSAPP === --}}
                        <a href="/whatsapp" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.message-square />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Whatsapp</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Whatsapp
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === CLIENTES === --}}
                        <a href="/clientes" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.user />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Clientes</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Clientes
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === PEDIDOS === --}}
                        <a href="/pedidos" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.file-text />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Pedidos</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Pedidos
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === SERVICIOS === --}}
                        <a href="/servicios" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.check-circle />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Servicios</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Servicios
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === INVENTARIO === --}}
                        <a href="/inventario" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.boxes />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Inventario</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Inventario
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === REPORTES === --}}
                        <a href="/reportes" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.bar-chart />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Reportes</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Reportes
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === HISTORIAL === --}}
                        <a href="/historial" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.history />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Historial</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Historial
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                        {{-- === CONFIGURACIÓN === --}}
                        <a href="/configuracion" wire:navigate class="sidebar-link group relative flex items-center"
                           :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                            <x-icons.settings />
                            <span x-show="!sidebarColapsada" class="transition-opacity">Configuración</span>
                            <div
                                :class="{ 'hidden': !sidebarColapsada }"
                                class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                                    Configuración
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0 border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                                </div>
                            </div>
                        </a>

                    </nav>


                </div>
            </div>

            <div class="pt-3 border-t border-white/10">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="sidebar-link group relative flex items-center mt-3"
                   :class="sidebarColapsada ? 'justify-center' : 'space-x-2'">
                    <x-icons.log-out />
                    <span x-show="!sidebarColapsada" class="transition-opacity">Cerrar sesión</span>

                    {{-- Tooltip flotante al colapsar --}}
                    <div
                        :class="{ 'hidden': !sidebarColapsada }"
                        class="absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                        <div class="relative bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-lg whitespace-nowrap">
                            Cerrar sesión
                            <div class="absolute left-0 top-1/2 -translate-y-1/2 -ml-2 w-0 h-0
                            border-y-6 border-y-transparent border-r-6 border-r-gray-800"></div>
                        </div>
                    </div>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>




        </div>
    </aside>
</div>
