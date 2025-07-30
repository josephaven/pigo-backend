@php($tabActivo = 'para-elaboracion')

@section('title', 'Pedidos para elaboración')

@section('tabs')
    @include('components.pedido-tabs', ['tabActivo' => $tabActivo])
@endsection

<div class="px-4 py-6 sm:px-6 font-[Poppins] space-y-6">


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
            </tr>
            </thead>
            <tbody>
            @forelse($pedidos as $pedido)
                <tr class="bg-white shadow-sm rounded" wire:key="pedido-{{ $pedido->id }}">
                    <td class="px-4 py-2 font-mono">{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 py-2">{{ $pedido->cliente->nombre_completo ?? '-' }}</td>
                    <td class="px-4 py-2">Impresión lona</td> {{-- temporal --}}
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">
                        @php($estado = $pedido->variantes->first()?->estado ?? 'en_espera')
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ match($estado) {
                                'registrado', 'en_espera' => 'bg-blue-100 text-blue-800',
                                'en_produccion' => 'bg-yellow-100 text-yellow-800',
                                'listo' => 'bg-lime-100 text-lime-800',
                                'entregado' => 'bg-green-100 text-green-800',
                                'cancelado' => 'bg-red-100 text-red-800',
                                'devuelto' => 'bg-orange-100 text-orange-800',
                                default => 'bg-gray-100 text-gray-800'
                            } }}">
                            {{ ucfirst(str_replace('_', ' ', $estado)) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right">${{ number_format($pedido->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No hay pedidos para elaborar en esta sucursal.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
