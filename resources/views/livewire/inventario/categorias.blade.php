@php($tabActivo = 'categorias')

@section('title', 'Inventario')

@section('tabs')
    @include('components.inventario-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <button
        onclick="window.dispatchEvent(new CustomEvent('abrir-modal-categoria'))"
        class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nueva categoría
    </button>
@endsection

<div class="p-4 sm:p-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-categoria', () => {
            Livewire.dispatch('abrirModal');
        });
    </script>

    {{-- Tarjetas de categorías --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @forelse($categorias as $categoria)
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-lg font-semibold mb-2">{{ $categoria->nombre }}</h3>
                <p class="text-sm text-gray-700 mb-2">{{ $categoria->descripcion ?: 'Sin descripción' }}</p>

                <div class="flex justify-end">
                    <button wire:click="editar({{ $categoria->id }})"
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
        @empty
            <div class="col-span-full text-center text-gray-500 mt-6">
                No hay categorías registradas aún.
            </div>
        @endforelse
    </div>

    {{-- Modal --}}
    @if($modal_abierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-xl w-full max-w-md mx-auto shadow-lg p-6"
                 wire:keydown.enter.prevent="guardar">

                <h2 class="text-lg font-semibold mb-4">
                    {{ $modo_edicion ? 'Editar categoría' : 'Nueva categoría' }}
                </h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" wire:model.defer="nombre"
                           class="w-full px-3 py-2 border rounded-md text-sm" />
                    @error('nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea wire:model.defer="descripcion" rows="3"
                              class="w-full px-3 py-2 border rounded-md text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-4">
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
