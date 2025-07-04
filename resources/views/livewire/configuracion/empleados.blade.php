<div class="p-4 sm:p-6 font-[Poppins]">
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="text-3xl font-bold">Configuración</h1>
        <button wire:click="abrirModal"
                class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm flex items-center gap-2 hover:bg-[#002f39] transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo empleado
        </button>
    </div>

    {{-- Filtros Responsivos --}}
    <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 mb-4">
        <input wire:model.defer="filtro_nombre" wire:key="{{ $filtroKey }}-nombre" type="text" placeholder="Nombre"
               class="col-span-1 border rounded-md px-3 py-2 text-sm w-full" />

        <select wire:model.defer="filtro_rol" wire:key="{{ $filtroKey }}-rol"
                class="col-span-1 border rounded-md px-3 py-2 text-sm w-full">
            <option value="">Rol</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
            @endforeach
        </select>

        <select wire:model.defer="filtro_estado" wire:key="{{ $filtroKey }}-estado"
                class="col-span-1 border rounded-md px-3 py-2 text-sm w-full">
            <option value="">Estado</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <select wire:model.defer="filtro_sucursal" wire:key="{{ $filtroKey }}-sucursal"
                class="col-span-1 border rounded-md px-3 py-2 text-sm w-full">
            <option value="">Sucursal</option>
            @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
            @endforeach
        </select>

        <div class="flex flex-col sm:flex-row gap-2 col-span-1 sm:col-span-2">
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
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl mx-4">
                <h2 class="text-xl sm:text-2xl font-bold mb-6">
                    {{ $modo_edicion ? 'Editar empleado' : 'Nuevo empleado' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Nombre</label>
                        <input wire:model.defer="nombre" type="text"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Usuario</label>
                        <input wire:model.defer="usuario" type="email"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Contraseña</label>
                        <input wire:model.defer="password" type="password"
                               class="w-full border rounded-md px-3 py-2 text-sm"
                               placeholder="{{ $modo_edicion ? 'Dejar en blanco para no cambiar' : 'Escriba una nueva contraseña' }}" />
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
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Salario</label>
                        <input wire:model.defer="salario" type="number"
                               class="w-full border rounded-md px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Estado</label>
                        <select wire:model.defer="estado"
                                class="w-full border rounded-md px-3 py-2 text-sm">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
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
