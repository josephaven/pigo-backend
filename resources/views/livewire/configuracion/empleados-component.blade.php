<x-layouts.app-pigo>
    <div class="p-6">
        {{-- Mensaje de confirmación --}}
        @if (session()->has('message'))
            <div class="mb-4 p-2 bg-green-100 border border-green-300 text-green-800 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Gestión de Empleados</h1>
            <button wire:click="$set('modalOpen', true)" class="bg-[#174F5C] text-white px-4 py-2 rounded hover:bg-[#143E47]">
                + Nuevo empleado
            </button>
        </div>

        {{-- Filtros --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <input type="text" wire:model.debounce.500ms="filtroNombre" placeholder="Buscar por nombre"
                class="border rounded px-3 py-1 w-full" />

            <select wire:model="filtroRol" class="border rounded px-3 py-1 w-full">
                <option value="">Rol</option>
                <option>Jefe</option>
                <option>Gerencia</option>
                <option>Atención al cliente</option>
            </select>

            <select wire:model="filtroSucursal" class="border rounded px-3 py-1 w-full">
                <option value="">Sucursal</option>
                <option>Centro</option>
                <option>Transismica</option>
            </select>

            <select wire:model="filtroEstado" class="border rounded px-3 py-1 w-full">
                <option value="">Estado</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>

            <button wire:click="limpiarFiltros" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded">
                Limpiar filtros
            </button>
        </div>

        {{-- Tabla de empleados --}}
        <div class="overflow-x-auto mt-2">
            <table class="min-w-full bg-white border border-gray-200 rounded shadow-sm">
                <thead class="bg-gray-100 text-gray-600 text-sm">
                    <tr>
                        <th class="px-4 py-2 border-b">Nombre</th>
                        <th class="px-4 py-2 border-b">Usuario</th>
                        <th class="px-4 py-2 border-b">Rol</th>
                        <th class="px-4 py-2 border-b">Sucursal</th>
                        <th class="px-4 py-2 border-b">Salario</th>
                        <th class="px-4 py-2 border-b">Estado</th>
                        <th class="px-4 py-2 border-b">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empleados as $empleado)
                        <tr class="text-sm text-gray-700 text-center hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">{{ $empleado->nombre }}</td>
                            <td class="px-4 py-2 border-b">{{ $empleado->email }}</td>
                            <td class="px-4 py-2 border-b">{{ $empleado->rol }}</td>
                            <td class="px-4 py-2 border-b">{{ $empleado->sucursal }}</td>
                            <td class="px-4 py-2 border-b">${{ number_format($empleado->salario, 2) }}</td>
                            <td class="px-4 py-2 border-b">
                                <span class="px-2 py-1 rounded text-xs font-semibold 
                                    {{ $empleado->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $empleado->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border-b">
                                <button wire:click="editarEmpleado({{ $empleado->id }})"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-400">No hay empleados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal --}}
        @if ($modalOpen)
            <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
                <div class="bg-white rounded-lg p-8 w-full max-w-lg border-2 border-purple-300">
                    <h2 class="text-2xl font-bold mb-6">
                        {{ $modoEdicion ? 'Editar empleado' : 'Nuevo empleado' }}
                    </h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm">Nombre</label>
                            <input type="text" wire:model="nombre" class="w-full border rounded px-3 py-2" />
                        </div>

                        <div>
                            <label class="block mb-1 text-sm">Usuario (email)</label>
                            <input type="email" wire:model="email" class="w-full border rounded px-3 py-2" />
                        </div>

                        @unless($modoEdicion)
                            <div>
                                <label class="block mb-1 text-sm">Contraseña</label>
                                <input type="password" wire:model="password" class="w-full border rounded px-3 py-2" />
                            </div>
                        @endunless

                        <div>
                            <label class="block mb-1 text-sm">Rol</label>
                            <select wire:model="rol" class="w-full border rounded px-3 py-2">
                                <option value="">Seleccione</option>
                                <option>Jefe</option>
                                <option>Gerencia</option>
                                <option>Atención al cliente</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm">Sucursal</label>
                            <select wire:model="sucursal" class="w-full border rounded px-3 py-2">
                                <option value="">Seleccione</option>
                                <option>Centro</option>
                                <option>Transismica</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 text-sm">Salario</label>
                            <input type="text" wire:model="salario" class="w-full border rounded px-3 py-2" />
                        </div>

                        @if ($modoEdicion)
                            <div>
                                <label class="block mb-1 text-sm">Estado</label>
                                <select wire:model="estado" class="w-full border rounded px-3 py-2">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button wire:click="$set('modalOpen', false)" class="px-5 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button wire:click="guardarEmpleado" class="px-5 py-2 bg-[#174F5C] text-white rounded hover:bg-[#143E47]">
                            {{ $modoEdicion ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app-pigo>
