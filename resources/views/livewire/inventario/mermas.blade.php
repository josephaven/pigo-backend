@php($tabActivo = 'mermas')

@section('title', 'Inventario')

@section('tabs')
    @include('components.inventario-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button onclick="window.dispatchEvent(new CustomEvent('abrir-modal-merma'))"
            class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Registrar merma
    </button>
@endsection

<div class="p-4 sm:p-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-merma', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- 🔍 Filtros --}}
    <div class="flex flex-wrap gap-4 mb-4 items-end">
        <input type="text" wire:model.defer="filtro_insumo" wire:key="{{ $filtroKey }}"
               class="px-3 py-2 rounded-md border border-gray-300 text-sm min-w-[160px]"
               placeholder="Nombre del insumo">

        <input type="text" wire:model.defer="filtro_usuario" wire:key="{{ $filtroKey }}"
               class="px-3 py-2 rounded-md border border-gray-300 text-sm min-w-[160px]"
               placeholder="Responsable">

        <input type="date" wire:model.defer="filtro_fecha" wire:key="{{ $filtroKey }}"
               class="px-3 py-2 rounded-md border border-gray-300 text-sm w-[160px]">

        <button wire:click="cargarMermas"
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

    {{-- 📦 Tabla de Mermas --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg mb-6">
        <table class="min-w-full text-sm text-left border-separate border-spacing-y-2">
            <thead class="bg-gray-100 text-gray-600">
            <tr>
                <th class="px-4 py-2 font-semibold">Fecha</th>
                <th class="px-4 py-2 font-semibold">Insumo</th>
                <th class="px-4 py-2 font-semibold">Variante</th>
                <th class="px-4 py-2 font-semibold">Cantidad</th>
                <th class="px-4 py-2 font-semibold">Justificación</th>
                <th class="px-4 py-2 font-semibold">Responsable</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>

            </tr>
            </thead>
            <tbody>
            @forelse($mermas as $merma)
                <tr class="bg-white shadow-sm rounded">
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($merma->fecha)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $merma->insumo?->nombre }}</td>
                    <td class="px-4 py-2">
                        {{ $merma->variante ? collect($merma->variante->atributos)->map(fn($v, $k) => ucfirst($k).': '.$v)->join(', ') : '-' }}
                    </td>
                    <td class="px-4 py-2">{{ $merma->cantidad }}</td>
                    <td class="px-4 py-2 max-w-[200px] truncate" title="{{ $merma->justificacion }}">
                        {{ $merma->justificacion ?? '-' }}
                    </td>
                    <td class="px-4 py-2">{{ $merma->user?->name }}</td>
                    <td class="px-4 py-2">
                        <div class="flex justify-end">
                            <button wire:click="editar({{ $merma->id }})"
                                    class="bg-[#003844] text-white px-3 py-1 rounded-md hover:bg-[#002f39] text-xs flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                                    <path d="m15 5 4 4" />
                                </svg>
                                Editar
                            </button>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">No hay mermas registradas.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ➕ Modal de Merma con buscador y tabla --}}
    {{-- ➕ Modal de Merma con buscador y tabla --}}
    @if($modal_abierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white w-full max-w-4xl mx-auto rounded-xl shadow-lg p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl font-semibold mb-4">
                    {{ $modo_edicion ? 'Editar justificación' : 'Registrar merma' }}
                </h2>

                {{-- 🔎 Buscador y tabla solo si NO estamos en edición --}}
                @unless($modo_edicion)
                    {{-- 🔍 Buscador --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Buscar insumo o variante</label>
                        <input type="text"
                               wire:model.debounce.500ms="insumoBuscado"
                               wire:keydown.enter="buscarInsumos"
                               placeholder="Buscar insumo o variante"
                               class="w-full mt-1 px-3 py-2 border rounded-md text-sm">

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
                                                   wire:model.defer="cantidadesMerma.{{ $clave }}"
                                                   class="w-24 px-2 py-1 border rounded text-sm text-center">
                                            @error('cantidadesMerma.' . $clave)
                                            <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                            @enderror
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
                @endunless

                {{-- 📄 Justificación y Fecha --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    @unless($modo_edicion)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
                            <input type="date" wire:model="fecha"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            @error('fecha') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endunless

                    <div class="sm:col-span-{{ $modo_edicion ? '2' : '1' }}">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Justificación</label>
                        <input type="text" wire:model="justificacion"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('justificacion') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- 🧷 Botones --}}
                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="cancelar"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                            class="px-4 py-2 bg-[#003844] text-white rounded-md text-sm hover:bg-[#002f39]">
                        {{ $modo_edicion ? 'Guardar cambios' : 'Guardar merma' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
