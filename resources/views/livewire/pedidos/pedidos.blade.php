@php($tabActivo = 'pedidos')

@section('title', 'Pedidos')

@section('tabs')
    @include('components.pedido-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <a href="{{ route('pedidos.nuevo') }}"
       class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center justify-center sm:justify-start gap-2 hover:bg-[#002f39] transition w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo pedido
    </a>
@endsection

<div class="px-4 py-4 sm:px-6 sm:py-6 font-[Poppins]">


    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
        <input type="text" wire:model.defer="filtro_folio" wire:key="{{ $filtroKey }}-folio"
               placeholder="Folio"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">

        <input type="text" wire:model.defer="filtro_cliente" wire:key="{{ $filtroKey }}-cliente"
               placeholder="Nombre cliente"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">

        <input type="date" wire:model.defer="filtro_fecha" wire:key="{{ $filtroKey }}-fecha"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">

        <select wire:model.defer="filtro_estado" wire:key="{{ $filtroKey }}-estado"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Estado</option>
            <option value="en_espera">Registrado</option>
            <option value="en_produccion">En producción</option>
            <option value="listo">Listo para entrega</option>
            <option value="entregado">Entregado</option>
            <option value="cancelado">Cancelado</option>
            <option value="devuelto">Devuelto</option>
        </select>

        {{-- Botones --}}
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
                <th class="px-4 py-2 font-semibold">Folio</th>
                <th class="px-4 py-2 font-semibold">Cliente</th>
                <th class="px-4 py-2 font-semibold">Descripción</th>
                <th class="px-4 py-2 font-semibold">Fecha entrega</th>
                <th class="px-4 py-2 font-semibold">Estado</th>
                <th class="px-4 py-2 font-semibold text-right">Total</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($pedidos as $pedido)
                <tr class="bg-white shadow-sm rounded" wire:key="pedido-{{ $pedido->id }}">
                    <td class="px-4 py-2 font-mono">{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 py-2">{{ $pedido->cliente->nombre_completo ?? '-' }}</td>
                    <td class="px-4 py-2 space-y-1">
                        @foreach($pedido->variantes as $variante)
                            <div class="text-xs truncate">
                                {{ $variante->nombre_personalizado ?? ($variante->servicio->nombre ?? 'Servicio no disponible') }}
                            </div>
                        @endforeach
                    </td>

                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 space-y-2">
                        @foreach($pedido->variantes as $variante)
                            <div class="flex justify-between items-center gap-4 text-sm" wire:key="variante-{{ $variante->id }}">
                                <span class="text-xs text-gray-700">{{ $variante->nombre_personalizado ?? ($variante->servicio->nombre ?? '-') }}</span>

                                <select
                                    wire:change="actualizarEstado({{ $variante->id }}, $event.target.value)"
                                    class="appearance-none pl-3 pr-6 py-1.5 text-xs rounded-full font-medium transition
                                    border-none focus:ring-2 focus:outline-none hover:cursor-pointer
                                    {{
                                        $variante->estado === 'registrado' || $variante->estado === 'en_espera' ? 'bg-blue-100 text-blue-800' :
                                        ($variante->estado === 'en_produccion' ? 'bg-yellow-100 text-yellow-800' :
                                        ($variante->estado === 'listo_para_entrega' || $variante->estado === 'listo' ? 'bg-lime-100 text-lime-800' :
                                        ($variante->estado === 'entregado' ? 'bg-green-100 text-green-800' :
                                        ($variante->estado === 'cancelado' ? 'bg-red-100 text-red-800' :
                                        ($variante->estado === 'devuelto' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')))))
                                    }}"
                                    style="min-width: 120px"
                                >
                                    <option value="registrado" {{ $variante->estado === 'registrado' ? 'selected' : '' }}>Registrado</option>
                                    <option value="en_produccion" {{ $variante->estado === 'en_produccion' ? 'selected' : '' }}>En producción</option>
                                    <option value="listo_para_entrega" {{ $variante->estado === 'listo_para_entrega' ? 'selected' : '' }}>Listo para entrega</option>
                                    <option value="entregado" {{ $variante->estado === 'entregado' ? 'selected' : '' }}>Entregado</option>
                                    <option value="cancelado" {{ $variante->estado === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                    <option value="devuelto" {{ $variante->estado === 'devuelto' ? 'selected' : '' }}>Devuelto</option>
                                </select>
                            </div>
                        @endforeach

                    </td>


                    <td class="px-4 py-2 text-right">${{ number_format($pedido->total, 2) }}</td>
                    <td class="px-4 py-2">
                        <div class="flex justify-end">
                            <a href="{{ route('pedidos.editar', $pedido->id) }}"
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
                    <td colspan="7" class="text-center py-4 text-gray-500">No hay pedidos registrados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($mostrar_modal_motivo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Motivo del cambio de estado</h2>
                <textarea wire:model.defer="motivo"
                          rows="4"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring focus:ring-blue-300"></textarea>

                <div class="flex justify-end mt-4 gap-2">
                    <button wire:click="guardarMotivo"
                            class="bg-[#003844] text-white px-4 py-2 rounded-md hover:bg-[#002f39] text-sm">
                        Guardar
                    </button>
                    <button wire:click="$set('mostrar_modal_motivo', false)"
                            class="text-gray-600 hover:underline text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
