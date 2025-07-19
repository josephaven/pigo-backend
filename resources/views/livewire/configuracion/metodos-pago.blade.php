@php($tabActivo = 'metodos')

@section('title', 'Configuración')

@section('tabs')
    @include('components.config-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button
        onclick="window.dispatchEvent(new CustomEvent('abrir-modal-metodo'))"
        class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo método
    </button>
@endsection

<div class="p-4 sm:p-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-metodo', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- Grid de tarjetas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($metodos as $metodo)
            <div class="bg-white shadow rounded-lg p-6 flex flex-col justify-between space-y-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $metodo->nombre }}</h2>

                    @if($metodo->descripcion)
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-600">Descripción:</span> {{ $metodo->descripcion }}
                        </p>
                    @endif

                    @if($metodo->tipo)
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-600">Tipo:</span> {{ ucfirst($metodo->tipo) }}
                        </p>
                    @endif

                    @if($metodo->banco)
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-600">Banco:</span> {{ $metodo->banco }}
                        </p>
                    @endif

                    @if($metodo->titular)
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-600">Titular:</span> {{ $metodo->titular }}
                        </p>
                    @endif

                    @if($metodo->cuenta)
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-600">Cuenta:</span> {{ $metodo->cuenta }}
                        </p>
                    @endif

                    @if($metodo->clabe)
                        <p class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-600">CLABE:</span> {{ $metodo->clabe }}
                        </p>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button wire:click="editar({{ $metodo->id }})"
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
    {{-- Modal --}}
    @if($modal_abierto)
        <div wire:key="{{ $modalKey }}" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-xl mx-4"
                 x-data
                 @keydown.enter.prevent="$wire.guardar()">

                <h2 class="text-xl sm:text-2xl font-bold mb-6">
                    {{ $modo_edicion ? 'Editar método de pago' : 'Nuevo método de pago' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="sm:col-span-2">
                        <label class="block text-sm mb-1">Nombre</label>
                        <input wire:model.defer="nombre" type="text" class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm mb-1">Descripción (opcional)</label>
                        <textarea wire:model.defer="descripcion" class="w-full border rounded-md px-3 py-2 text-sm resize-none" rows="2"></textarea>
                        @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm mb-1">Tipo</label>
                        <select wire:model="tipo" class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="">Selecciona un tipo</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="terminal">Terminal</option>
                        </select>

                        @error('tipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Banco</label>
                        <input wire:model.defer="banco" type="text" class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('banco') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Cuenta</label>
                        <input wire:model.defer="cuenta" type="text" class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('cuenta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">CLABE</label>
                        <input wire:model.defer="clabe" type="text" class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('clabe') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Titular</label>
                        <input wire:model.defer="titular" type="text" class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('titular') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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

</div>
