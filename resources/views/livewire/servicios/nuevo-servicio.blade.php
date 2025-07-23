
@section('title', $modo_edicion ? 'Editar servicio' : 'Nuevo servicio')

<div class="p-4 sm:p-6 font-[Poppins] max-w-5xl mx-auto">

    {{-- Título y botones --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">
            {{ $modo_edicion ? 'Editar servicio' : 'Crear nuevo servicio' }}
        </h2>

        <a href="{{ route('servicios') }}"
           class="text-sm text-gray-600 hover:text-gray-800 hover:underline flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Volver al listado
        </a>
    </div>

    {{-- FORMULARIO: DATOS GENERALES --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-6 border border-gray-200">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del servicio</label>
                <input type="text" wire:model.defer="nombre"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring focus:ring-blue-100">
                @error('nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Tipo de cobro --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de cobro</label>
                <select wire:model.defer="tipo_cobro"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="pieza">Pieza</option>
                    <option value="m2">Metro cuadrado</option>
                    <option value="ml">Metro lineal</option>
                    <option value="otro">Otro</option>
                </select>
                @error('tipo_cobro') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Precio normal --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio cliente normal</label>
                <input type="number" step="0.01" wire:model.defer="precio_normal"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('precio_normal') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Precio maquilador --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio maquilador</label>
                <input type="number" step="0.01" wire:model.defer="precio_maquilador"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('precio_maquilador') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Precio mínimo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio mínimo (opcional)</label>
                <input type="number" step="0.01" wire:model.defer="precio_minimo"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('precio_minimo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Usar cobro mínimo --}}
            <div class="flex items-center mt-6">
                <input type="checkbox" wire:model.defer="usar_cobro_minimo"
                       class="mr-2 rounded border-gray-300">
                <label class="text-sm text-gray-700">Aplicar precio mínimo al calcular</label>
            </div>

            {{-- Estado --}}
            <div class="flex items-center mt-6">
                <input type="checkbox" wire:model.defer="activo"
                       class="mr-2 rounded border-gray-300">
                <label class="text-sm text-gray-700">Servicio activo</label>
            </div>
        </div>
    </div>

    {{-- BOTONES --}}
    <div class="flex justify-end mt-6 gap-4">
        <a href="{{ route('servicios') }}"
           class="px-4 py-2 rounded-md border border-gray-300 text-sm hover:bg-gray-100">
            Cancelar
        </a>

        <button wire:click="guardar"
                class="bg-[#003844] text-white px-5 py-2 rounded-md text-sm hover:bg-[#002f39] transition">
            Guardar servicio
        </button>
    </div>

</div>
