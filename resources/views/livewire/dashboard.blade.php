<div class="py-10 px-4 sm:px-6 max-w-7xl mx-auto font-[Poppins]">

    {{-- Encabezado: Bienvenida + Rol + Selector --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white text-center sm:text-left">
            ¡Bienvenido, {{ Auth::user()->name }}!
        </h2>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            <span class="font-semibold text-center sm:text-left">
                {{ Auth::user()->rol->nombre ?? 'Rol desconocido' }}
            </span>

            @if($esJefe)
                <div class="relative w-full max-w-[180px] sm:max-w-[12rem] mx-auto sm:mx-0">
                <form method="POST" action="{{ route('cambiar-sucursal') }}">
                        @csrf
                        <select name="sucursal_id" onchange="this.form.submit()"
                                class="w-full appearance-none border border-gray-300 text-sm px-4 py-1 rounded bg-white dark:bg-gray-800 dark:text-white focus:outline-none focus:ring pr-6 truncate"
                                style="background-image: none;">
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ session('sucursal_activa_id') == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre }}
                                </option>
                            @endforeach
                        </select>

                </form>

                    <div class="absolute right-2 top-2 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            @else
                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs text-center">
                    {{ $sucursalNombre }}
                </span>
            @endif
        </div>
    </div>

    {{-- Botones de acción --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <a href="#"
           class="bg-[#5B3CF1] hover:bg-[#472cd5] text-white px-4 py-2 rounded shadow text-sm text-center">
            + Nuevo pedido
        </a>
        <a href="#"
           class="bg-[#5B3CF1] hover:bg-[#472cd5] text-white px-4 py-2 rounded shadow text-sm text-center">
            + Nuevo cliente
        </a>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach([
            ['label' => 'Pedidos del día', 'valor' => '5 pedidos', 'color' => 'text-blue-600'],
            ['label' => 'Ganancias del día', 'valor' => '$1,245.00 MXN', 'color' => 'text-green-600'],
            ['label' => 'Pedidos pendientes', 'valor' => '10 pedidos', 'color' => 'text-gray-700'],
            ['label' => 'Insumos en stock', 'valor' => '120 insumos', 'color' => 'text-gray-700'],
            ['label' => 'Alertas de insumos', 'valor' => '5 en nivel mínimo', 'color' => 'text-red-600'],
            ['label' => 'Mermas registradas', 'valor' => '2 hoy', 'color' => 'text-black'],
        ] as $card)
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow flex flex-col justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-300">{{ $card['label'] }}</p>
                <h3 class="text-2xl font-bold {{ $card['color'] }}">{{ $card['valor'] }}</h3>
            </div>
        @endforeach
    </div>

    {{-- Script para recargar al cambiar sucursal --}}
    <script>
        let cambiandoSucursal = false;

        window.addEventListener('sucursal-cambiada', () => {
            cambiandoSucursal = true;
            document.body.style.pointerEvents = 'none';
            window.location.href = "/dashboard";
        });
    </script>

</div>
