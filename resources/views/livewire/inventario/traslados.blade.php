@php($tabActivo = 'traslados')

@section('title', 'Inventario')

@section('tabs')
    @include('components.inventario-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button onclick="window.dispatchEvent(new CustomEvent('abrir-modal-traslado'))"
            class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo traslado
    </button>
@endsection

<div class="p-4 sm:p-6 font-[Poppins]">
    {{-- Script para abrir el modal --}}
    <script>
        window.addEventListener('abrir-modal-traslado', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- 📦 TABLA PRINCIPAL DE TRASLADOS --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg mb-6">
        <table class="min-w-full text-xs sm:text-sm text-left border-separate border-spacing-y-2">
            <thead class="text-gray-600 bg-gray-100">
            <tr>
                <th class="px-4 py-2 font-semibold">Fecha</th>
                <th class="px-4 py-2 font-semibold">Origen</th>
                <th class="px-4 py-2 font-semibold">Destino</th>
                <th class="px-4 py-2 font-semibold">Responsable</th>
                <th class="px-4 py-2 font-semibold">Estado</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($traslados as $traslado)
                <tr class="bg-white shadow-sm rounded">
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($traslado->fecha_solicitud)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $traslado->sucursalOrigen->nombre }}</td>
                    <td class="px-4 py-2">{{ $traslado->sucursalDestino->nombre }}</td>
                    <td class="px-4 py-2">{{ $traslado->user->name }}</td>

                    {{-- 🟡 SELECT INLINE PARA CAMBIAR ESTADO --}}
                    <td class="px-4 py-2">
                        <div class="relative inline-block">
                            <select wire:change="actualizarEstado({{ $traslado->id }}, $event.target.value)"
                                    class="appearance-none pl-3 pr-6 py-1.5 text-xs rounded-full font-medium transition
                       border-none focus:ring-2 focus:outline-none
                       bg-yellow-100 text-yellow-800
                       hover:cursor-pointer"
                                    style="min-width: 100px">
                                <option value="pendiente" {{ $traslado->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="enviado" {{ $traslado->estado === 'enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="recibido" {{ $traslado->estado === 'recibido' ? 'selected' : '' }}>Recibido</option>
                            </select>



                        </div>
                    </td>




                    {{-- 🔎 BOTÓN PARA VER DETALLES --}}
                    <td class="px-4 py-2 text-right">
                        <div class="flex justify-end gap-2">
                            <button wire:click="verDetalles({{ $traslado->id }})"
                                    class="bg-[#003844] text-white px-3 py-1 rounded-md hover:bg-[#002f39] text-xs flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                                Ver detalles
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">Sin traslados registrados</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📄 PAGINACIÓN --}}
    <div class="mt-4">
        {{ $traslados->links() }}
    </div>


    {{-- 🔍 MODAL DE DETALLES DE TRASLADO --}}
    @if($modal_detalles_abierto && $trasladoSeleccionado)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-xl w-full max-w-2xl mx-auto shadow-lg p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-semibold mb-4">Detalles del traslado</h2>

                <p class="text-sm text-gray-700 mb-2">
                    <strong>Origen:</strong> {{ $trasladoSeleccionado->sucursalOrigen->nombre }} <br>
                    <strong>Destino:</strong> {{ $trasladoSeleccionado->sucursalDestino->nombre }} <br>
                    <strong>Responsable:</strong> {{ $trasladoSeleccionado->user->name }} <br>
                    <strong>Estado:</strong> {{ ucfirst($trasladoSeleccionado->estado) }}
                </p>

                <table class="w-full text-sm border-separate border-spacing-y-2 mt-4">
                    <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Insumo / Variante</th>
                        <th class="px-3 py-2 text-left">Cantidad</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($trasladoSeleccionado->detalles as $detalle)
                        <tr class="bg-white">
                            <td class="px-3 py-2">
                                @if($detalle->insumo)
                                    {{ $detalle->insumo->nombre }}
                                @elseif($detalle->varianteInsumo)
                                    {{ $detalle->varianteInsumo->insumo->nombre }}
                                    ({{ collect(json_decode($detalle->varianteInsumo->atributos, true))->map(fn($v, $k) => "$k: $v")->join(' / ') }})
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $detalle->cantidad }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="mt-6 text-right">
                    <button wire:click="$set('modal_detalles_abierto', false)"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm hover:bg-gray-300">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ➕ MODAL DE NUEVO TRASLADO --}}
    @if($modal_abierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
             wire:key="modal-traslados-{{ now()->timestamp }}">
            <div class="bg-white rounded-xl w-full max-w-4xl mx-auto shadow-lg p-6 overflow-y-auto max-h-[95vh]">

                <h2 class="text-xl font-semibold mb-4">Nuevo traslado</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium">Sucursal origen</label>
                        <select wire:model="sucursal_origen_id" class="w-full px-3 py-2 border rounded-md text-sm">
                            <option value="">Selecciona...</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Sucursal destino</label>
                        <select wire:model="sucursal_destino_id" class="w-full px-3 py-2 border rounded-md text-sm">
                            <option value="">Selecciona...</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 🧾 Tabla de insumos y variantes --}}
                <div class="overflow-x-auto border rounded-md mt-4">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-100 text-gray-600">
                        <tr>
                            <th class="px-3 py-2">Insumo / Variante</th>
                            <th class="px-3 py-2">Stock</th>
                            <th class="px-3 py-2">Cantidad</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($insumos as $insumo)
                            @if(!$insumo->tiene_variantes)
                                <tr>
                                    <td class="px-3 py-2">{{ $insumo->nombre }}</td>
                                    <td class="px-3 py-2">{{ $insumo->stockSucursales->first()?->cantidad_actual ?? 0 }}</td>
                                    <td class="px-3 py-2">
                                        <input type="number" min="0"
                                               wire:model.defer="cantidadesTraslado.insumo-{{ $insumo->id }}"
                                               class="w-full px-2 py-1 border rounded text-sm" />
                                    </td>
                                </tr>
                            @else
                                @foreach($insumo->variantes as $variante)
                                    <tr class="bg-gray-50">
                                        <td class="px-3 py-2">
                                            {{ $insumo->nombre }} (
                                            {{ implode(' / ', collect($variante->atributos)->map(fn($v, $k) => "$k: $v")->toArray()) }}
                                            )
                                        </td>
                                        <td class="px-3 py-2">{{ $variante->stockSucursales->first()?->cantidad_actual ?? 0 }}</td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0"
                                                   wire:model.defer="cantidadesTraslado.variante-{{ $variante->id }}"
                                                   class="w-full px-2 py-1 border rounded text-sm" />
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="cerrarModal"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                            class="px-4 py-2 bg-[#003844] text-white rounded-md text-sm hover:bg-[#002f39]">
                        Guardar traslado
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- 🔔 Toasts de notificación --}}
    @include('components.toast')
</div>
