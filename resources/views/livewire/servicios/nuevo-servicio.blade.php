
@section('title', $modo_edicion ? 'Editar servicio' : 'Nuevo servicio')

<div class="p-4 sm:p-6 font-[Poppins] max-w-5xl mx-auto">

    {{-- Título y botones --}}
    <div class="flex justify-between items-center mb-6">


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

    {{-- VISIBILIDAD --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-4 border border-gray-200 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Visibilidad</h2>

        {{-- Sucursales agregadas --}}
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach ($sucursales_seleccionadas as $id)
                @foreach ($sucursales_disponibles as $sucursal)
                    @if ($sucursal->id == $id)
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center gap-1">
                        {{ $sucursal->nombre }}
                        <button type="button" wire:click="quitarSucursal({{ $id }})"
                                class="hover:text-red-600 font-bold">×</button>
                    </span>
                    @endif
                @endforeach
            @endforeach
        </div>

        {{-- Selector --}}
        <div class="flex gap-2">
            <select wire:model="sucursal_a_agregar"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option selected hidden value="">Selecciona una sucursal para agregar</option>
                @foreach ($sucursales_disponibles as $sucursal)
                    @if (!in_array($sucursal->id, $sucursales_seleccionadas, true))
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endif
                @endforeach
            </select>


            <button type="button" wire:click="agregarSucursal"
                    class="bg-[#003844] text-white px-4 py-2 rounded-md text-sm hover:bg-[#002f39]">
                Agregar
            </button>
        </div>


        @error('sucursales_seleccionadas')
        <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>

    {{-- INSUMOS ASOCIADOS --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-4 border border-gray-200 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Insumos asociados</h2>

        {{-- Agregar insumo --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end" wire:key="formulario-insumo-{{ count($insumos_agregados) }}">
        {{-- Selector de insumo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Insumo</label>
                <select wire:model="insumo_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option selected hidden value="">Selecciona un insumo</option>
                    @foreach ($insumos_disponibles as $insumo)
                        @if (!in_array($insumo->id, array_column($insumos_agregados, 'id')))
                            <option value="{{ $insumo->id }}">
                                {{ $insumo->nombre }} ({{ $insumo->categoria->nombre ?? 'Sin categoría' }})
                            </option>
                        @endif
                    @endforeach
                </select>

                @error('insumo_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Cantidad --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                <input type="number" step="0.01" wire:model.defer="cantidad_insumo"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('cantidad_insumo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            {{-- Unidad con datalist --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de medida</label>
                <input type="text" list="unidades" wire:model.defer="unidad_insumo"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />

                <datalist id="unidades">
                    @foreach($this->unidadesExistentes as $unidad)
                        <option value="{{ $unidad }}">{{ $unidad }}</option>
                    @endforeach
                </datalist>

                @error('unidad_insumo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>


            {{-- Botón agregar --}}
            <div>
                <button type="button" wire:click="agregarInsumo"
                        class="bg-[#003844] text-white w-full px-4 py-2 rounded-md text-sm hover:bg-[#002f39]">
                    Agregar insumo
                </button>
            </div>
        </div>

        {{-- Tabla de insumos agregados --}}
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full text-sm text-left border border-gray-300">
                <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="px-4 py-2 border">Nombre</th>
                    <th class="px-4 py-2 border">Categoría</th>
                    <th class="px-4 py-2 border">Cantidad</th>
                    <th class="px-4 py-2 border">Unidad</th>
                    <th class="px-4 py-2 border text-center">Acción</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($insumos_agregados as $insumo)
                    <tr class="border-t">
                        <td class="px-4 py-2 border">{{ $insumo['nombre'] }}</td>
                        <td class="px-4 py-2 border">{{ $insumo['categoria'] }}</td>
                        <td class="px-4 py-2 border">{{ $insumo['cantidad'] }}</td>
                        <td class="px-4 py-2 border">{{ $insumo['unidad'] }}</td>
                        <td class="px-4 py-2 border text-center">
                            <button type="button" wire:click="quitarInsumo({{ $insumo['id'] }})"
                                    class="text-red-600 hover:underline text-xs">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No se han agregado insumos.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Error si no hay insumos --}}
        @error('insumos_agregados')
        <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>


    {{-- CAMPOS PERSONALIZADOS --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-4 border border-gray-200 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Campos personalizados para el cliente</h2>

        {{-- Lista de campos ya agregados --}}
        @if (count($campos_personalizados) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-300">
                    <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border">Nombre</th>
                        <th class="px-4 py-2 border">Tipo</th>
                        <th class="px-4 py-2 border">Opciones</th>
                        <th class="px-4 py-2 border">Requerido</th>
                        <th class="px-4 py-2 border">Activo</th>
                        <th class="px-4 py-2 border text-center">Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($campos_personalizados as $i => $campo)
                        <tr class="border-t">
                            <td class="px-4 py-2 border">{{ $campo['nombre'] }}</td>
                            <td class="px-4 py-2 border capitalize">{{ $campo['tipo'] }}</td>
                            <td class="px-4 py-2 border">
                                @if($campo['tipo'] === 'select')
                                    {{ implode(', ', $campo['opciones']) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 border text-center">
                                <span class="text-sm">{{ $campo['requerido'] ? 'Sí' : 'No' }}</span>
                            </td>
                            <td class="px-4 py-2 border text-center">
                                <span class="text-sm">{{ $campo['activo'] ? 'Sí' : 'No' }}</span>
                            </td>
                            <td class="px-4 py-2 border text-center">
                                <button type="button" wire:click="quitarCampoPersonalizado({{ $i }})"
                                        class="text-red-600 hover:underline text-xs">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Formulario para agregar campo personalizado --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end"
             wire:key="formulario-campo-{{ count($campos_personalizados) }}">

        {{-- Nombre del campo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del campo</label>
                <input type="text" wire:model.defer="campo_nombre"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                @error('campo_nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                @error('nombre_repetido')
                <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror

            </div>

            {{-- Tipo de campo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model.lazy="campo_tipo"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="texto">Texto</option>
                    <option value="numero">Número</option>
                    <option value="booleano">Sí/No</option>
                    <option value="select">Lista de opciones</option>
                </select>
                @error('campo_tipo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>


            {{-- Opciones (si es select) --}}
            @if($campo_tipo === 'select')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opciones (si aplica)</label>
                    <input type="text" wire:model="campo_opciones"
                           placeholder="Ej. Opción A, Opción B"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    @error('campo_opciones')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            @endif


            {{-- Requerido y Activo --}}
            <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center text-sm text-gray-700">
                    <input type="checkbox" wire:model.defer="campo_requerido" class="mr-2 rounded">
                    Requerido
                </label>
                <label class="flex items-center text-sm text-gray-700">
                    <input type="checkbox" wire:model.defer="campo_activo" class="mr-2 rounded">
                    Activo
                </label>
            </div>

            {{-- Botón agregar --}}
            <div class="col-span-1 sm:col-span-4">
                <button type="button" wire:click="agregarCampoPersonalizado"
                        class="bg-[#003844] text-white px-5 py-2 rounded-md text-sm hover:bg-[#002f39] w-full sm:w-auto">
                    Agregar campo
                </button>
            </div>
        </div>

        @error('campos_personalizados')
        <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>


    {{-- Vista previa del servicio --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-4 border border-gray-200 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Vista previa del servicio</h2>

        {{-- Datos generales --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
            <p><span class="font-medium text-gray-800">Nombre:</span> {{ $nombre }}</p>
            <p><span class="font-medium text-gray-800">Tipo de cobro:</span> {{ ucfirst($tipo_cobro) }}</p>
            <p><span class="font-medium text-gray-800">Precio normal:</span> ${{ number_format($precio_normal, 2) }}</p>
            <p><span class="font-medium text-gray-800">Precio maquilador:</span> ${{ number_format($precio_maquilador, 2) }}</p>
            @if ($usar_cobro_minimo)
                <p><span class="font-medium text-gray-800">Precio mínimo:</span> ${{ number_format($precio_minimo, 2) }}</p>
            @endif
            <p><span class="font-medium text-gray-800">Estado:</span> {{ $activo ? 'Activo' : 'Inactivo' }}</p>
        </div>

        {{-- Sucursales --}}
        <div>
            <p class="text-sm font-semibold text-gray-800 mb-1">Sucursales asociadas:</p>
            <ul class="list-disc list-inside text-sm text-gray-700">
                @forelse ($sucursales_disponibles as $sucursal)
                    @if(in_array($sucursal->id, $sucursales_seleccionadas))
                        <li>{{ $sucursal->nombre }}</li>
                    @endif
                @empty
                    <li class="text-gray-500 italic">Ninguna sucursal seleccionada.</li>
                @endforelse
            </ul>
        </div>

        {{-- Insumos --}}
        @if (count($insumos_agregados))
            <div>
                <p class="text-sm font-semibold text-gray-800 mb-1">Insumos requeridos:</p>
                <ul class="list-disc list-inside text-sm text-gray-700">
                    @foreach ($insumos_agregados as $insumo)
                        <li>{{ $insumo['nombre'] }} — {{ $insumo['cantidad'] }} {{ $insumo['unidad'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Campos personalizados --}}
        @if (count($campos_personalizados))
            <div>
                <p class="text-sm font-semibold text-gray-800 mb-1">Campos personalizados del cliente:</p>
                <ul class="list-disc list-inside text-sm text-gray-700">
                    @foreach ($campos_personalizados as $campo)
                        <li>
                            <span class="font-medium">{{ $campo['nombre'] }}</span>
                            <span class="text-gray-600">({{ ucfirst($campo['tipo']) }})</span>
                            @if($campo['requerido'])
                                <span class="text-red-600 font-semibold ml-1">[Requerido]</span>
                            @endif
                            @if($campo['tipo'] === 'select')
                                <br><span class="text-gray-600 ml-2">Opciones: {{ implode(', ', $campo['opciones']) }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
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
