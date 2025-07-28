@php($tabActivo = 'sucursales')

@section('title', 'Configuración')

@section('tabs')
    @include('components.config-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button
        onclick="window.dispatchEvent(new CustomEvent('abrir-modal-sucursal'))"
        class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center justify-center sm:justify-start gap-2 hover:bg-[#002f39] transition w-full sm:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nueva sucursal
    </button>
@endsection

<div class="px-4 py-4 sm:px-6 sm:py-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-sucursal', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- Grid de tarjetas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($sucursales as $sucursal)
            <div class="bg-white shadow rounded-lg p-6 flex flex-col justify-between space-y-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $sucursal->nombre }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ $sucursal->calle_numero }}, {{ $sucursal->colonia }}<br>
                        {{ $sucursal->municipio }}, {{ $sucursal->estado }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-600">Telefono:</span>{{ $sucursal->telefono }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-600">Apertura:</span> {{ \Carbon\Carbon::parse($sucursal->fecha_apertura)->format('d/m/Y') }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-600">Empleados:</span> {{ $sucursal->empleados_count }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <button wire:click="editar({{ $sucursal->id }})"
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
            </div>
        @endforeach
    </div>

    {{-- Modal --}}
    @if($modal_abierto)
        <div wire:key="{{ $modalKey }}"
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4"
                 x-data
                 @keydown.enter.prevent="$wire.guardar()">

            <h2 class="text-xl sm:text-2xl font-bold mb-6">
                    {{ $modo_edicion ? 'Editar sucursal' : 'Nueva sucursal' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Nombre</label>
                        <input wire:model.defer="nombre" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Calle y número</label>
                        <input wire:model.defer="calle_numero" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('calle_numero') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Colonia</label>
                        <input wire:model.defer="colonia" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('colonia') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Municipio</label>
                        <input wire:model.defer="municipio" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('municipio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Estado</label>
                        <input wire:model.defer="estado" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('estado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Teléfono</label>
                        <input wire:model.defer="telefono" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('telefono') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Fecha de apertura</label>
                        <input wire:model.defer="fecha_apertura" type="date"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('fecha_apertura') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-2 sm:gap-4">
                    <button wire:click="cerrarModal"
                            class="px-4 py-2 rounded-md bg-gray-200 text-gray-800 text-sm hover:bg-gray-300 w-full sm:w-auto">
                        Cancelar
                    </button>
                    <button wire:click="guardar"
                            class="px-4 py-2 rounded-md bg-[#003844] text-white text-sm hover:bg-[#002f39] w-full sm:w-auto">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @include('components.toast')
</div>
