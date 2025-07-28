@php($tabActivo = 'empleados')

@section('title', 'Configuración')

@section('tabs')
    @include('components.config-tabs', ['tabActivo' => $tabActivo])
@endsection

@section('action')
    <div class="w-full sm:w-auto">
        <button
            onclick="window.dispatchEvent(new CustomEvent('abrir-modal-empleado'))"
            class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center justify-center sm:justify-start gap-2 hover:bg-[#002f39] transition w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo empleado
        </button>
    </div>
@endsection


<div class="px-4 py-4 sm:px-6 sm:py-6 font-[Poppins]">
    <script>
        window.addEventListener('abrir-modal-empleado', () => {
            Livewire.dispatch('abrirModalExterno');
        });
    </script>

    {{-- Filtros Responsivos --}}
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
        <input wire:model.defer="filtro_nombre" wire:key="{{ $filtroKey }}-nombre" type="text" placeholder="Nombre"
               class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full" />

        <select wire:model.defer="filtro_rol" wire:key="{{ $filtroKey }}-rol"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Rol</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
            @endforeach
        </select>

        <select wire:model.defer="filtro_estado" wire:key="{{ $filtroKey }}-estado"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Estado</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <select wire:model.defer="filtro_sucursal" wire:key="{{ $filtroKey }}-sucursal"
                class="col-span-1 border border-gray-300 rounded-md px-3 py-2 text-sm w-full">
            <option value="">Sucursal</option>
            @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
            @endforeach
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
    <div class="overflow-x-auto w-full bg-white shadow rounded-lg">
        <table class="min-w-full text-xs sm:text-sm text-left border-separate border-spacing-y-2">
            <thead class="text-gray-600 bg-gray-100">
            <tr>
                <th class="px-4 py-2 font-semibold">Nombre</th>
                <th class="px-4 py-2 font-semibold">Usuario</th>
                <th class="px-4 py-2 font-semibold">Rol</th>
                <th class="px-4 py-2 font-semibold">Sucursal</th>
                <th class="px-4 py-2 font-semibold">Estado</th>
                <th class="px-4 py-2 font-semibold text-right">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($empleados as $empleado)
                <tr class="bg-white shadow-sm rounded" wire:key="empleado-{{ $empleado->id }}">
                    <td class="px-4 py-2">{{ $empleado->name }}</td>
                    <td class="px-4 py-2">{{ $empleado->email }}</td>
                    <td class="px-4 py-2">{{ $empleado->rol->nombre }}</td>
                    <td class="px-4 py-2">{{ $empleado->sucursal->nombre }}</td>
                    <td class="px-4 py-2">
                        @if ($empleado->estado)
                            <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Activo</span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex justify-end">
                            <button wire:click="editar({{ $empleado->id }})"
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
        <div wire:key="{{ $modalKey }}"
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4 sm:mx-auto"
                 x-data
                 @keydown.enter.prevent="$wire.guardar()">

                <h2 class="text-xl sm:text-2xl font-bold mb-6 text-center sm:text-left">
                    {{ $modo_edicion ? 'Editar empleado' : 'Nuevo empleado' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Nombre</label>
                        <input wire:model.defer="nombre" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Usuario</label>
                        <input wire:model.defer="usuario" type="email"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('usuario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Contraseña</label>
                        <input wire:model.defer="password" type="password"
                               class="w-full border rounded-md px-3 py-2 text-sm"
                               placeholder="{{ $modo_edicion ? 'Dejar en blanco para no cambiar' : 'Escriba una nueva contraseña' }}" />
                        @error('password')
                        @if (!$modo_edicion || ($modo_edicion && $password))
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @endif
                        @enderror

                    </div>

                    <div>
                        <label class="block text-sm mb-1">Rol</label>
                        <select wire:model.defer="rol_id"
                                class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="">Selecciona...</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        @error('rol_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Sucursal</label>
                        <select wire:model.defer="sucursal_id"
                                class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="">Selecciona...</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sucursal_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Salario</label>
                        <input wire:model.defer="salario" type="number"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('salario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Teléfono</label>
                        <input wire:model.defer="telefono" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('telefono') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Dirección</label>
                        <input wire:model.defer="direccion" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('direccion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">RFC</label>
                        <input wire:model.defer="rfc" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                        @error('rfc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Estado</label>
                        <select wire:model.defer="estado"
                                class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                        @error('estado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
