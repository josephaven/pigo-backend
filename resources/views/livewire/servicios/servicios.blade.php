
@php($tabActivo = 'servicios')

@section('title', 'Servicios')

@section('action')
    <a href="{{ route('servicios.nuevo') }}"
       class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center justify-center sm:justify-start gap-2 hover:bg-[#002f39] transition w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo servicio
    </a>
@endsection

<div class="px-4 py-4 sm:px-6 sm:py-6 font-[Poppins]">

    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">

        <input type="text" wire:model.defer="filtro_nombre" wire:key="{{ $filtroKey }}-nombre"
               placeholder="Nombre del servicio"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">

        <select wire:model.defer="filtro_cobro" wire:key="{{ $filtroKey }}-cobro"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Tipo de cobro</option>
            <option value="pieza">Pieza</option>
            <option value="m2">Metro cuadrado</option>
            <option value="ml">Metro lineal</option>
            <option value="otro">Otro</option>
        </select>

        <select wire:model.defer="filtro_estado" wire:key="{{ $filtroKey }}-estado"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Estado</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <div class="flex flex-col sm:flex-row gap-2 col-span-1 md:col-span-2">
            <button wire:click="filtrar"
                    class="bg-[#003844] text-white px-4 py-2 rounded-md text-xs sm:text-sm flex items-center justify-center gap-2 hover:bg-[#002f39] transition w-full sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21 21-4.34-4.34" />
                    <circle cx="11" cy="11" r="8" />
                </svg>
                Buscar
            </button>

            <button wire:click="limpiarFiltros"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md text-xs sm:text-sm flex items-center justify-center gap-2 hover:bg-gray-300 transition w-full sm:w-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m16 22-1-4" />
                    <path d="M19 13.99a1 1 0 0 0 1-1V12a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v.99a1 1 0 0 0 1 1" />
                    <path d="M5 14h14l1.973 6.767A1 1 0 0 1 20 22H4a1 1 0 0 1-.973-1.233z" />
                    <path d="m8 22 1-4" />
                </svg>
                Limpiar
            </button>
        </div>

    </div>


    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-xs sm:text-sm text-left border-separate border-spacing-y-2">
            <thead class="text-gray-600 bg-gray-100">
            <tr>
                <th class="px-4 py-2 font-semibold">Nombre</th>
                <th class="px-4 py-2 font-semibold text-center">Tipo de cobro</th>
                <th class="px-4 py-2 font-semibold text-center">Precio N</th>
                <th class="px-4 py-2 font-semibold text-center">Precio M</th>
                <th class="px-4 py-2 font-semibold text-center">Estado</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($servicios as $servicio)
                <tr class="bg-white shadow-sm rounded" wire:key="servicio-{{ $servicio->id }}">
                    <td class="px-4 py-2">{{ $servicio->nombre }}</td>
                    <td class="px-4 py-2 text-center uppercase">{{ $servicio->tipo_cobro }}</td>
                    <td class="px-4 py-2 text-center">${{ number_format($servicio->precio_normal, 2) }}</td>
                    <td class="px-4 py-2 text-center">${{ number_format($servicio->precio_maquilador, 2) }}</td>
                    <td class="px-4 py-2 text-center">
                        @if ($servicio->activo)
                            <span class="text-green-600 font-semibold">Activo</span>
                        @else
                            <span class="text-red-600 font-semibold">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('servicios.editar', $servicio->id) }}"
                               class="bg-[#003844] text-white px-3 py-1 rounded-md hover:bg-[#002f39] text-xs flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                    <path d="m15 5 4 4" />
                                </svg>
                                Editar
                            </a>


                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No hay servicios registrados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            if (window.__bridgeSucursalServicios) return;
            window.__bridgeSucursalServicios = true;

            // El Dashboard emite este evento cuando cambia la sucursal activa
            window.addEventListener('sucursal-cambiada', function () {
                // Avisamos a Livewire que refresque este componente
                Livewire.dispatch('sucursalActualizada');
            });
        })();
    </script>
@endpush

