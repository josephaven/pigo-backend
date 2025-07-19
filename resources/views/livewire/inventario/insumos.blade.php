@php($tabActivo = 'insumos')

@section('title', 'Inventario')

@section('tabs')
    @include('components.inventario-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button
        onclick="window.dispatchEvent(new CustomEvent('abrir-modal-insumo'))"
        class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo insumo
    </button>
@endsection


<div class="p-4 sm:p-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-insumo', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>



    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <input type="text" wire:model.defer="filtro_nombre" wire:key="{{ $filtroKey }}-nombre" placeholder="Nombre"
               class="border border-gray-300 rounded-md px-3 py-2 text-sm">

        <select wire:model.defer="filtro_categoria" wire:key="{{ $filtroKey }}-categoria"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm">
            <option value="">Categoría</option>
            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
            @endforeach
        </select>

        <select wire:model.defer="filtro_alerta" wire:key="{{ $filtroKey }}-alerta"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm">
            <option value="">Alertas</option>
            <option value="Normal">Normal</option>
            <option value="Bajo stock">Bajo stock</option>
            <option value="Sin stock">Sin stock</option>

        </select>

        <div class="flex gap-2">
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
    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-xs sm:text-sm text-left border-separate border-spacing-y-2">
            <thead class="text-gray-600 bg-gray-100">
            <tr>
                <th class="px-4 py-2 font-semibold">Insumo</th>
                <th class="px-4 py-2 font-semibold">Categoría</th>
                <th class="px-4 py-2 font-semibold">Unidad</th>
                <th class="px-4 py-2 font-semibold">Stock</th>
                <th class="px-4 py-2 font-semibold">Alertas</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($insumos as $insumo)
                <tr class="bg-white shadow-sm rounded" wire:key="insumo-{{ $insumo->id }}">
                    <td class="px-4 py-2">{{ $insumo->nombre }}</td>
                    <td class="px-4 py-2">{{ $insumo->categoria->nombre ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $insumo->unidad_medida }}</td>
                    <td class="px-4 py-2">
                        {{ (int) $insumo->stock_de_sucursal }}
                    </td>
                    <td class="px-4 py-2">
                        @switch($insumo->alerta_stock)
                            @case('Sin stock')
                                <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Sin stock</span>
                                @break
                            @case('Bajo stock')
                                <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">Bajo stock</span>
                                @break
                            @default
                                <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Normal</span>
                        @endswitch
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex justify-end gap-2">
                            @if($insumo->tiene_variantes)
                                <button wire:click="toggleVariantes({{ $insumo->id }})"
                                        class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md text-xs hover:bg-blue-200">
                                    {{ in_array($insumo->id, $filasConVariantesAbiertas) ? 'Ocultar' : 'Ver variantes' }}
                                </button>
                            @endif

                            <button wire:click="editar({{ $insumo->id }})"
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

                @if(in_array($insumo->id, $filasConVariantesAbiertas))
                    <tr class="bg-gray-50 border-t" wire:key="variantes-{{ $insumo->id }}">
                        <td colspan="6" class="px-6 py-4">
                            <div class="text-sm font-semibold mb-2">Variantes de {{ $insumo->nombre }}:</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs border-separate border-spacing-y-1">
                                    <thead>
                                    <tr class="bg-gray-200 text-gray-700">
                                        @foreach($insumo->variantes->first()?->atributos ?? [] as $atributo => $valor)
                                            <th class="px-3 py-1 capitalize">{{ $atributo }}</th>
                                        @endforeach
                                        <th class="px-3 py-1">Sucursal</th>
                                        <th class="px-3 py-1">Stock actual</th>
                                        <th class="px-3 py-1">Stock mínimo</th>
                                        <th class="px-3 py-1">Alerta</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($insumo->variantes as $variante)
                                        @foreach($variante->stockSucursales as $stock)
                                            <tr class="bg-white">
                                                @foreach($variante->atributos as $valor)
                                                    <td class="px-3 py-1">{{ $valor }}</td>
                                                @endforeach
                                                <td class="px-3 py-1">{{ $stock->sucursal->nombre }}</td>
                                                <td class="px-3 py-1">{{ (int) $stock->cantidad_actual }}</td>
                                                <td class="px-3 py-1">{{ (int) $stock->stock_minimo }}</td>
                                                <td class="px-3 py-1">
                                                    @if($stock->cantidad_actual == 0)
                                                        <span class="text-red-600 font-medium">Sin stock</span>
                                                    @elseif($stock->cantidad_actual < $stock->stock_minimo)
                                                        <span class="text-yellow-600 font-medium">Bajo stock</span>
                                                    @else
                                                        <span class="text-green-600 font-medium">Normal</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-400">No hay insumos registrados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>





    {{-- Modal separado --}}
    {{-- Modal separado --}}
    @if($modal_abierto)
        <div wire:key="modal-insumo-{{ $filtroKey }}" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-xl w-full max-w-4xl mx-auto shadow-lg p-6 overflow-y-auto max-h-[95vh]"
                 wire:keydown.enter.prevent="guardar">

                <h2 class="text-xl font-semibold mb-4">
                    {{ $modo_edicion ? 'Editar insumo' : 'Nuevo insumo' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" wire:model.defer="nombre"
                               class="w-full px-3 py-2 border rounded-md text-sm" />
                        @error('nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Unidad de medida</label>
                        <input type="text" list="unidades" wire:model.defer="unidad_medida"
                               class="w-full px-3 py-2 border rounded-md text-sm" />

                        <datalist id="unidades">
                            @foreach($unidadesExistentes as $unidad)
                                <option value="{{ $unidad }}">{{ $unidad }}</option>
                            @endforeach
                        </datalist>

                        @error('unidad_medida')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>


                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea wire:model.defer="descripcion" rows="2"
                                  class="w-full px-3 py-2 border rounded-md text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                        <select wire:model.defer="categoria_insumo_id"
                                class="w-full px-3 py-2 border rounded-md text-sm">
                            <option value="">Selecciona una</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_insumo_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>



                    <div class="sm:col-span-2 flex items-center gap-2 mt-2">
                        <input type="checkbox" wire:model.lazy="tiene_variantes"
                               id="tiene_variantes" class="rounded border-gray-300">
                        <label for="tiene_variantes" class="text-sm text-gray-700">
                            Este insumo tiene variantes (talla, color...)
                        </label>
                    </div>
                </div>

                @if(!$tiene_variantes)
                    <div class="sm:col-span-2 mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Stock por sucursal</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($sucursales as $sucursal)
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">{{ $sucursal->nombre }}</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-0.5">{{ $sucursal->nombre }} (Stock actual)</label>
                                            <input type="number"
                                                   wire:model.defer="stockInicial.{{ $sucursal->id }}"
                                                   class="w-full px-2 py-1 border rounded-md text-sm"
                                                   min="0" placeholder="0" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-0.5">{{ $sucursal->nombre }} (Stock mínimo)</label>
                                            <input type="number"
                                                   wire:model.defer="stockMinimoPorSucursal.{{ $sucursal->id }}"
                                                   class="w-full px-2 py-1 border rounded-md text-sm"
                                                   min="0" placeholder="0" />
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($tiene_variantes)
                    <div class="mt-6 space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700">Atributos de variantes</h3>
                        <div class="space-y-2">
                            @foreach ($atributos as $index => $nombreAtributo)
                                <div class="mb-4 border p-3 rounded bg-gray-50" wire:key="atributo-card-{{ md5($nombreAtributo . '-' . $index) }}">
                                <div class="flex justify-between items-center mb-2">
                                        <input type="text" wire:model="atributos.{{ $index }}"
                                               placeholder="Nombre del atributo"
                                               class="w-full px-3 py-1 border rounded text-sm mr-2" />
                                        <button type="button" class="text-sm text-red-600"
                                                wire:click="eliminarAtributoPorIndice({{ $index }})">
                                            Eliminar
                                        </button>
                                    </div>

                                    @if (isset($atributos[$index]) && strlen($atributos[$index]) > 0)
                                        <div class="space-y-2" wire:key="atributo-{{ md5($nombreAtributo) }}">
                                        {{-- Mostrar valores solo si existen --}}
                                            @if (!empty($valoresAtributos[$atributos[$index]]))
                                                @php($claveAtributo = $atributos[$index] ?? '')

                                                @php($claveAtributo = $atributos[$index] ?? '')
                                                @foreach ($valoresAtributos[$claveAtributo] as $valIndex => $valor)
                                                    @php($valorKey = $valor ?? uniqid())
                                                    <div class="flex items-center gap-2" wire:key="valor-{{ md5($claveAtributo . '-' . $valorKey) }}">

                                                    <input type="text"
                                                           wire:model.lazy="valoresAtributos.{{ $claveAtributo }}.{{ $valIndex }}"
                                                           wire:blur="generarCombinaciones"
                                                               class="w-full px-2 py-1 border rounded text-sm" />

                                                        <button type="button"
                                                                wire:click="eliminarValor('{{ $atributos[$index] }}', {{ $valIndex }})"
                                                                class="text-xs text-red-600 hover:underline">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif

                                            {{-- Mostrar botón siempre si el atributo tiene nombre --}}
                                            <button type="button"
                                                    class="text-blue-600 text-sm hover:underline"
                                                    wire:click="agregarValorPorIndice({{ $index }})">
                                                + Agregar valor a {{ $atributos[$index] ?? 'Atributo ' . ($index + 1) }}
                                            </button>
                                        </div>
                                    @endif


                                </div>
                            @endforeach

                            <button type="button" wire:click="agregarAtributo"
                                    class="mt-2 text-sm px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition">
                                + Agregar atributo
                            </button>
                        </div>

                        <hr class="my-4" />

                        <h3 class="text-sm font-semibold text-gray-700">Combinaciones</h3>

                        @foreach($combinaciones as $index => $combo)
                            <div class="border rounded-md p-3 bg-gray-50 space-y-2" wire:key="combo-{{ $index }}">
                                <div class="grid grid-cols-2 sm:grid-cols-{{ count($combo) }} gap-2">
                                    @foreach($combo as $clave => $valor)
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">{{ ucfirst($clave) }}</label>
                                            <input type="text" value="{{ $valor }}" class="w-full px-2 py-1 border rounded-md text-sm bg-gray-100" disabled>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($sucursales as $sucursal)
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-0.5">{{ $sucursal->nombre }} (Stock actual)</label>
                                            <input type="number"
                                                   wire:model.defer="stockPorVariante.{{ $index }}.{{ $sucursal->id }}"
                                                   class="w-full px-2 py-1 border rounded-md text-sm"
                                                   min="0" placeholder="0" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-0.5">{{ $sucursal->nombre }} (Stock mínimo)</label>
                                            <input type="number"
                                                   wire:model.defer="stockMinimoPorVariante.{{ $index }}.{{ $sucursal->id }}"
                                                   class="w-full px-2 py-1 border rounded-md text-sm"
                                                   min="0" placeholder="0" />
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-2 mt-6">
                    <button wire:click="cerrarModal"
                            class="px-4 py-2 bg-gray-200 rounded-md text-sm hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                            class="px-4 py-2 bg-[#003844] text-white rounded-md text-sm hover:bg-[#002f39]">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
