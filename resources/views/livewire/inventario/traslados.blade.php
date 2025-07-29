@php($tabActivo = 'traslados')

@section('title', 'Inventario')

@section('tabs')
    @include('components.inventario-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button onclick="window.dispatchEvent(new CustomEvent('abrir-modal-traslado'))"
            class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center justify-center sm:justify-start gap-2 hover:bg-[#002f39] transition w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo traslado
    </button>
@endsection

<div class="px-4 py-4 sm:px-6 sm:py-6 font-[Poppins]">
    {{-- Script para abrir el modal --}}
    <script>
        window.addEventListener('abrir-modal-traslado', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- 🔍 Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
        <select wire:model="filtro_origen" wire:key="{{ $filtroKey }}"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Sucursal origen</option>
            @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
            @endforeach
        </select>

        <select wire:model="filtro_destino" wire:key="{{ $filtroKey }}"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Sucursal destino</option>
            @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
            @endforeach
        </select>

        <select wire:model="filtro_estado" wire:key="{{ $filtroKey }}"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Estado</option>
            <option value="pendiente">Pendiente</option>
            <option value="enviado">Enviado</option>
            <option value="recibido">Recibido</option>
        </select>

        <input type="text" wire:model="filtro_usuario" wire:key="{{ $filtroKey }}"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full"
               placeholder="Responsable">

        <input type="date" wire:model="filtro_fecha" wire:key="{{ $filtroKey }}"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">



        <div class="flex flex-col sm:flex-row gap-2 col-span-1 md:col-span-2">
            <button wire:click="$refresh"
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



    {{-- 📦 TABLA PRINCIPAL DE TRASLADOS --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg mb-6">
        <table class="min-w-full text-xs sm:text-sm text-left border-separate border-spacing-y-2">
            <thead class="text-gray-600 bg-gray-100">
            <tr>
                <th class="px-4 py-2 font-semibold">Fecha</th>
                <th class="px-4 py-2 font-semibold">Origen</th>
                <th class="px-4 py-2 font-semibold">Destino</th>
                <th class="px-4 py-2 font-semibold">Responsable</th>
                <th class="px-4 py-2 font-semibold">Insumos</th>
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
                    <td class="px-4 py-2 text-sm text-gray-700">
                        @php($detalles = $traslado->detalles ?? [])
                        @php($contador = 0)
                        @php($maxVisibles = 3)
                        @php($total = count($detalles))

                        @if($total > 0)
                            <ul class="list-disc list-inside space-y-0.5 text-xs">
                                @foreach($detalles as $detalle)
                                    @break($contador >= $maxVisibles)

                                    @if($detalle->insumo)
                                        <li class="truncate">{{ $detalle->insumo->nombre }}</li>
                                    @elseif($detalle->variante && $detalle->variante->insumo)
                                        <li class="truncate">
                                            {{ $detalle->variante->insumo->nombre }}
                                            @if($detalle->variante->atributos)

                                                @foreach(json_decode($detalle->variante->atributos, true) as $k => $v)
                                                    {{ $k }}: {{ $v }}@if(!$loop->last), @endif
                                                @endforeach

                                            @endif
                                        </li>
                                    @endif

                                    @php($contador++)
                                @endforeach
                            </ul>

                            @if($total > $maxVisibles)
                                <div class="text-xs text-gray-500 italic mt-1">+{{ $total - $maxVisibles }} más</div>
                            @endif
                        @else
                            <span class="text-xs text-gray-400 italic">Sin insumos</span>
                        @endif
                    </td>



                    {{-- 🟡 SELECT INLINE PARA CAMBIAR ESTADO --}}
                    <td class="px-4 py-2">
                        <div class="relative inline-block">
                            <select wire:change="actualizarEstado({{ $traslado->id }}, $event.target.value)"
                                    class="appearance-none pl-3 pr-6 py-1.5 text-xs rounded-full font-medium transition
               border-none focus:ring-2 focus:outline-none hover:cursor-pointer
               {{
                   $traslado->estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
                   ($traslado->estado === 'enviado' ? 'bg-blue-100 text-blue-800' :
                   ($traslado->estado === 'recibido' ? 'bg-green-100 text-green-800' :
                   ($traslado->estado === 'cancelado' ? 'bg-red-100 text-red-800' : '')))
               }}"
                                    style="min-width: 100px">
                                <option value="pendiente" {{ $traslado->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="enviado" {{ $traslado->estado === 'enviado' ? 'selected' : '' }}>Enviado</option>
                                <option value="recibido" {{ $traslado->estado === 'recibido' ? 'selected' : '' }}>Recibido</option>
                                <option value="cancelado" {{ $traslado->estado === 'cancelado' ? 'selected' : '' }}>Cancelado</option>

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
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($trasladoSeleccionado->fecha_solicitud)->format('d/m/Y') }} <br>
                    <strong>Origen:</strong> {{ $trasladoSeleccionado->sucursalOrigen->nombre }} <br>
                    <strong>Destino:</strong> {{ $trasladoSeleccionado->sucursalDestino->nombre }} <br>
                    <strong>Responsable:</strong> {{ $trasladoSeleccionado->user->name }} <br>
                    <strong>Estado:</strong> {{ ucfirst($trasladoSeleccionado->estado) }} <br>
                    @if($trasladoSeleccionado->estado !== 'pendiente' && $trasladoSeleccionado->estadoActualizadoPor)
                        @if($trasladoSeleccionado->estado === 'recibido')
                            <strong>Recibió:</strong> {{ $trasladoSeleccionado->estadoActualizadoPor->name }}
                        @elseif($trasladoSeleccionado->estado === 'cancelado')
                            <strong>Canceló:</strong> {{ $trasladoSeleccionado->estadoActualizadoPor->name }}
                        @else
                            <strong>Envió:</strong> {{ $trasladoSeleccionado->estadoActualizadoPor->name }}
                        @endif
                    @endif

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

                {{-- 🔁 Selección de sucursales --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium">Sucursal origen</label>
                        <select wire:model="sucursal_origen_id" class="w-full px-3 py-2 border rounded-md text-sm">
                            @error('sucursal_origen_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <option value="">Selecciona...</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_origen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                    </div>
                    <div>
                        <label class="block text-sm font-medium">Sucursal destino</label>
                        <select wire:model="sucursal_destino_id" class="w-full px-3 py-2 border rounded-md text-sm">
                            <option value="">Selecciona...</option>
                            @foreach($sucursales as $sucursal)
                                @if($sucursal->id != $sucursal_origen_id)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('sucursal_destino_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                    </div>
                </div>

                {{-- 🔎 Buscador de insumos --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium">Buscar insumo o variante</label>
                    <input type="text"
                           wire:model.debounce.500ms="insumoBuscado"
                           wire:keydown.enter="buscarInsumos"
                           placeholder="Escribe el nombre o atributo del insumo"
                           class="w-full mt-1 px-3 py-2 border rounded-md text-sm">



                    {{-- 📋 Resultados --}}
                    @if(!empty($insumosDisponibles))
                        <div class="mt-2 border rounded-md bg-white shadow-sm max-h-48 overflow-y-auto">
                            @foreach($insumosDisponibles as $insumo)
                                @if($insumo['tiene_variantes'])
                                    @foreach($insumo['variantes'] as $variante)
                                        <div wire:click="agregarInsumo('variante', {{ $variante['id'] }})"
                                             class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b">
                                            {{ $insumo['nombre'] }}
                                            @foreach($variante['atributos'] as $k => $v)
                                                ({{ $k }}: {{ $v }})
                                            @endforeach
                                            – Stock: {{ $variante['stock'] }}
                                        </div>
                                    @endforeach
                                @else
                                    <div wire:click="agregarInsumo('insumo', {{ $insumo['id'] }})"
                                         class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b">
                                        {{ $insumo['nombre'] }} – Stock: {{ $insumo['stock'] }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ✅ Tabla de insumos seleccionados --}}
                @if(!empty($insumosSeleccionados))
                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="px-3 py-2">Insumo / Variante</th>
                                <th class="px-3 py-2 text-center">Stock</th>
                                <th class="px-3 py-2 text-center">Cantidad</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($insumosSeleccionados as $clave => $insumo)
                                <tr class="border-t">
                                    <td class="px-3 py-2">
                                        {{ $insumo['nombre'] }}
                                        @if(isset($insumo['atributos']))
                                            @foreach($insumo['atributos'] as $k => $v)
                                                ({{ $k }}: {{ $v }})
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-center">{{ $insumo['stock'] }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" min="0" step="0.01"
                                               wire:model.defer="cantidadesTraslado.{{ $clave }}"
                                               class="w-24 px-2 py-1 border rounded text-sm text-center">
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button wire:click="quitarInsumo('{{ $clave }}')"
                                                class="text-red-600 hover:text-red-800 text-xs font-semibold">
                                            Quitar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- 🧷 Botones de acción --}}
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
