@php($tabActivo = 'clientes')

@section('title', 'Clientes')


@section('action')
    <button
        onclick="window.dispatchEvent(new CustomEvent('abrir-modal-cliente'))"
        class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo cliente
    </button>
@endsection

<div class="p-4 sm:p-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-cliente', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-4 mb-4 items-end">
        <input wire:model.defer="filtro_nombre" type="text" placeholder="Nombre"
               class="border rounded-md px-3 py-2 text-sm w-[160px]" />

        <select wire:model.defer="filtro_tipo"
                class="border rounded-md px-3 py-2 text-sm w-[120px]">
            <option value="">Tipo</option>
            <option value="Normal">Normal</option>
            <option value="Frecuente">Frecuente</option>
            <option value="Maquilador">Maquilador</option>
        </select>

        <input wire:model.defer="filtro_telefono" type="text" placeholder="Teléfono"
               class="border rounded-md px-3 py-2 text-sm w-[120px]" />

        <input wire:model.defer="filtro_ocupacion" type="text" placeholder="Ocupación"
               class="border rounded-md px-3 py-2 text-sm w-[160px]" />

        <select wire:model.defer="filtro_mes_nacimiento"
                class="border rounded-md px-3 py-2 text-sm w-[155px]">
            <option value="">Mes nacimiento</option>
            <option value="01">Enero</option>
            <option value="02">Febrero</option>
            <option value="03">Marzo</option>
            <option value="04">Abril</option>
            <option value="05">Mayo</option>
            <option value="06">Junio</option>
            <option value="07">Julio</option>
            <option value="08">Agosto</option>
            <option value="09">Septiembre</option>
            <option value="10">Octubre</option>
            <option value="11">Noviembre</option>
            <option value="12">Diciembre</option>
        </select>

        <select wire:model.defer="filtro_anio_nacimiento"
                class="border rounded-md px-3 py-2 text-sm w-[150px]">
            <option value="">Año nacimiento</option>
            @for ($a = now()->year; $a >= 1950; $a--)
                <option value="{{ $a }}">{{ $a }}</option>
            @endfor
        </select>

        <div class="flex gap-2 w-full sm:w-auto">
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
                <th class="px-4 py-2 font-semibold">Teléfono</th>
                <th class="px-4 py-2 font-semibold">Tipo</th>
                <th class="px-4 py-2 font-semibold">Ocupación</th>
                <th class="px-4 py-2 font-semibold">Sucursal</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($clientes as $cliente)
                <tr class="bg-white shadow-sm rounded" wire:key="cliente-{{ $cliente->id }}">
                    <td class="px-4 py-2">{{ $cliente->nombre_completo }}</td>
                    <td class="px-4 py-2">{{ $cliente->telefono }}</td>
                    <td class="px-4 py-2">{{ $cliente->tipo_cliente }}</td>
                    <td class="px-4 py-2">{{ $cliente->ocupacion }}</td>
                    <td class="px-4 py-2">{{ $cliente->sucursal->nombre }}</td>
                    <td class="px-4 py-2">
                        <div class="flex justify-end">
                            <button wire:click="editar({{ $cliente->id }})"
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
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($modal_abierto)
        <div wire:key="{{ $modalKey }}" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4">
                <h2 class="text-xl sm:text-2xl font-bold mb-6">
                    {{ $modo_edicion ? 'Editar cliente' : 'Nuevo cliente' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Nombre completo</label>
                        <input wire:model.defer="nombre_completo" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Teléfono</label>
                        <input wire:model.defer="telefono" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Tipo</label>
                        <select wire:model.defer="tipo_cliente"
                                class="w-full min-w-full border rounded-md px-3 py-2 text-sm">
                            <option value="Normal">Normal</option>
                            <option value="Maquilador">Maquilador</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Ocupación</label>
                        <input list="ocupaciones" wire:model.defer="ocupacion"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        <datalist id="ocupaciones">
                            @foreach($ocupacionesUnicas as $ocu)
                                <option value="{{ $ocu }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fecha de nacimiento</label>
                        <input wire:model.defer="fecha_nacimiento" type="date"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                    </div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-2">
                    <button wire:click="cerrarModal"
                            class="px-4 py-2 rounded-md bg-gray-200 text-gray-800 text-sm hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                            class="px-4 py-2 rounded-md bg-[#003844] text-white text-sm hover:bg-[#002f39]">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @include('components.toast')
</div>
