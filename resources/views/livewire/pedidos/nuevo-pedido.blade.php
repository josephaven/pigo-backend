@section('title', $modo_edicion ? 'Editar pedido' : 'Nuevo pedido')

<div class="px-4 py-6 sm:px-6 font-[Poppins] space-y-6">

    {{-- Sección: Datos generales --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-6 border border-gray-200">

        <h2 class="text-lg font-semibold text-gray-800">Datos cliente</h2>

        {{-- Cliente --}}
        <div class="space-y-4">

            {{-- Checkbox cliente nuevo --}}
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:click="$set('cliente_nuevo', !@js($cliente_nuevo))" class="rounded border-gray-300">
                Cliente nuevo
            </label>


            {{-- Buscador tipo Google --}}
            <div class="{{ $cliente_nuevo ? 'hidden' : '' }}">
                <span class="hidden">{{ $forzar_render }}</span>

                <div class="relative">
                    <label class="block text-sm text-gray-700 mb-1">Buscar cliente</label>
                    <input type="text"
                           wire:model="busqueda_cliente"
                           wire:keyup="actualizarSugerencias"
                           placeholder="Nombre o teléfono..."
                           autocomplete="off"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">

                    {{-- Render forzado invisible para que Livewire detecte cambios --}}
                    <span class="hidden">{{ $forzar_render }}</span>

                    <ul class="absolute z-50 bg-white border rounded shadow w-full mt-1 max-h-48 overflow-auto
                   {{ $mostrar_sugerencias && $clientes_sugeridos->isNotEmpty() ? '' : 'hidden' }}">
                        @foreach ($clientes_sugeridos as $c)
                            <li wire:click="seleccionarCliente({{ $c->id }})"
                                class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer">
                                {{ $c->nombre_completo }} – {{ $c->telefono }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                @error('cliente_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror

                {{-- Vista previa del cliente seleccionado --}}
                @if ($cliente_seleccionado)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 text-sm text-gray-800 bg-gray-50 p-4 rounded border">
                        <div>
                            <p class="font-semibold text-gray-600">Nombre completo</p>
                            <p>{{ $cliente_seleccionado['nombre_completo'] }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600">Tipo cliente</p>
                            <p>{{ $cliente_seleccionado['tipo_cliente'] }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600">Teléfono</p>
                            <p>{{ $cliente_seleccionado['telefono'] }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600">Ocupación</p>
                            <p>{{ $cliente_seleccionado['ocupacion'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-600">Fecha de nacimiento</p>
                            <p>
                                {{ $cliente_seleccionado['fecha_nacimiento']
                                    ? \Carbon\Carbon::parse($cliente_seleccionado['fecha_nacimiento'])->format('d/m/Y')
                                    : '—' }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>


            {{-- Formulario para nuevo cliente --}}
            <div class="{{ !$cliente_nuevo ? 'hidden' : '' }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Nombre completo</label>
                        <input type="text" wire:model.defer="nombre_cliente"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('nombre_cliente') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Tipo cliente</label>
                        <select wire:model.defer="tipo_cliente"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="Normal">Normal</option>
                            <option value="Frecuente">Frecuente</option>
                            <option value="Maquilador">Maquilador</option>
                        </select>
                        @error('tipo_cliente') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Teléfono</label>
                        <input type="text" wire:model.defer="telefono_cliente"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('telefono_cliente') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Ocupación</label>
                        <input list="ocupaciones" wire:model.defer="ocupacion_cliente"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <datalist id="ocupaciones">
                            @foreach (\App\Models\Cliente::whereNotNull('ocupacion')->select('ocupacion')->distinct()->pluck('ocupacion') as $ocupacion)
                                <option value="{{ $ocupacion }}">
                            @endforeach
                        </datalist>
                        @error('ocupacion_cliente') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Fecha nacimiento</label>
                        <input type="date" wire:model.defer="fecha_nacimiento"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('fecha_nacimiento') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>


    </div>

    {{-- Sección: Datos generales --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-6 border border-gray-200">

        <h2 class="text-lg font-semibold text-gray-800">Detalles</h2>

        {{-- Fecha de entrega --}}
        <div>
            <label class="block text-sm text-gray-700 mb-1">Fecha de entrega</label>
            <input type="date" wire:model.defer="fecha_entrega"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
            @error('fecha_entrega') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Sucursales --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Sucursal de entrega</label>
                <select wire:model.defer="sucursal_entrega_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">Selecciona una</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
                @error('sucursal_entrega_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm text-gray-700 mb-1">Sucursal de elaboración</label>
                <select wire:model.defer="sucursal_elaboracion_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">Selecciona una</option>
                    @foreach ($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
                @error('sucursal_elaboracion_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm text-gray-700 mb-1">Sucursal que registra</label>
                <input type="text"
                       value="{{ $sucursales->firstWhere('id', $sucursal_registro_id)?->nombre }}"
                       readonly
                       class="w-full bg-gray-100 text-gray-700 border border-gray-300 rounded-md px-3 py-2 text-sm">
            </div>
        </div>


        {{-- Método de pago --}}
        <div>
            <label class="block text-sm text-gray-700 mb-1">Método de pago</label>
            <select wire:model.defer="metodo_pago_id"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option value="">Selecciona uno</option>
                @foreach ($metodos_pago as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                @endforeach
            </select>
            @error('metodo_pago_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Facturación --}}
        <div class="space-y-4 mt-6">

            {{-- Checkbox: Requiere factura --}}
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:click="$set('requiere_factura', !@js($requiere_factura))" class="rounded border-gray-300">
                ¿Requiere factura?
            </label>


            {{-- Formulario fiscal si se activa --}}
            @if($requiere_factura)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Razón social</label>
                        <input type="text" wire:model.defer="razon_social"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('razon_social') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">RFC</label>
                        <input type="text" wire:model.defer="rfc"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm uppercase">
                        @error('rfc') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm text-gray-700 mb-1">Dirección fiscal</label>
                        <input type="text" wire:model.defer="direccion_fiscal"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('direccion_fiscal') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Uso de CFDI</label>
                        <input type="text" wire:model.defer="uso_cfdi"
                               placeholder="Ej. G03"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('uso_cfdi') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">Método de pago (factura)</label>
                        <input type="text" wire:model.defer="metodo_pago_factura"
                               placeholder="Ej. PUE, PPD"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('metodo_pago_factura') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                    </div>

                </div>
            @endif
        </div>

    </div>



    {{-- Sección: Servicios del pedido --}}
    <div class="bg-white rounded-lg shadow p-6 space-y-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Servicios del pedido</h2>

        {{-- Switch para activar servicio personalizado --}}
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox"
                   wire:click="$set('servicio_personalizado', !@js($servicio_personalizado))"
                   class="rounded border-gray-300">
            Crear servicio personalizado
        </label>

        {{-- ===== SERVICIO PERSONALIZADO ===== --}}
        @if ($servicio_personalizado)
            {{-- Datos básicos del servicio --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-6">
                    <label class="block text-sm text-gray-700 mb-1">Nombre del servicio</label>
                    <input type="text" wire:model.defer="servicio_personalizado_nombre"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    @error('servicio_personalizado_nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-6">
                    <label class="block text-sm text-gray-700 mb-1">Precio unitario</label>
                    <input type="number" min="0" wire:model.defer="servicio_personalizado_precio"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    @error('servicio_personalizado_precio') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-12">
                    <label class="block text-sm text-gray-700 mb-1">Descripción (opcional)</label>
                    <textarea wire:model.defer="servicio_personalizado_descripcion"
                              rows="2"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"></textarea>
                </div>
            </div>

            {{-- Insumos del servicio personalizado --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-800">Insumos agregados</h3>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end w-full" wire:key="formulario-insumo-{{ count($insumos_agregados) }}">
                    <div class="relative col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar insumo</label>
                        <input type="text"
                               wire:model="busqueda_insumo"
                               wire:keyup="actualizarSugerenciasInsumo"
                               placeholder="Nombre del insumo..."
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <span class="hidden">{{ $forzar_render_insumo }}</span>

                        {{-- Dropdown de sugerencias --}}
                        <ul class="absolute left-0 right-0 z-50 bg-white border rounded shadow mt-1 max-h-48 overflow-auto
                               {{ $mostrar_sugerencias_insumo && $insumos_sugeridos ? '' : 'hidden' }}">
                            @foreach ($insumos_sugeridos as $i)
                                <li wire:click="seleccionarInsumo({{ $i->id }})"
                                    class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer">
                                    {{ $i->nombre }} ({{ $i->categoria->nombre ?? 'Sin categoría' }})
                                </li>
                            @endforeach

                        </ul>
                        @error('insumo_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                        <input type="number" step="0.01" wire:model.defer="cantidad_insumo"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @error('cantidad_insumo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

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

                    <div>
                        <button type="button" wire:click="agregarInsumo"
                                class="bg-[#003844] text-white w-full px-4 py-2 rounded-md text-sm hover:bg-[#002f39]">
                            Agregar insumo
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
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
                                <td class="px-4 py-2 border">{{ $insumo['categoria'] ?? 'Sin categoría' }}</td>
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
            </div>

            {{-- Toggle de campos personalizados --}}
            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox"
                           wire:click="$set('usar_campos_personalizados', !@js($usar_campos_personalizados))"
                           class="rounded border-gray-300">
                    ¿Usar campos personalizados?
                </label>
            </div>

            {{-- Constructor de campos personalizados --}}
            @if ($usar_campos_personalizados)
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-800">Campos personalizados para el cliente</h3>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Nombre del campo</label>
                            <input type="text" wire:model.defer="nuevoCampo.nombre"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            @error('nuevoCampo.nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Tipo</label>
                            <select wire:model="nuevoCampo.tipo"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">Selecciona tipo</option>
                                <option value="texto">Texto</option>
                                <option value="numero">Número</option>
                                <option value="booleano">Sí/No</option>
                                <option value="select">Lista de opciones</option>
                            </select>
                            @error('nuevoCampo.tipo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Opciones</label>
                            <input type="text" wire:model.defer="nuevoCampo.opciones"
                                   placeholder="Ej: opción1, opción2"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            @error('nuevoCampo.opciones') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <button type="button" wire:click="agregarCampoPersonalizado"
                                    class="w-full bg-[#003844] text-white px-4 py-2 rounded-md text-sm hover:bg-[#002f39]">
                                Agregar campo
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left border border-gray-300">
                            <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2">Nombre</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">Opciones</th>
                                <th class="px-4 py-2 text-center">Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($campos_personalizados_temporales as $index => $campo)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $campo['nombre'] }}</td>
                                    <td class="px-4 py-2">
                                        @switch($campo['tipo'])
                                            @case('texto') Texto @break
                                            @case('numero') Número @break
                                            @case('booleano') Sí/No @break
                                            @case('select') Lista de opciones @break
                                            @default {{ ucfirst($campo['tipo'] ?? '') }}
                                        @endswitch
                                    </td>

                                    <td class="px-4 py-2">
                                        @if ($campo['tipo'] === 'select')
                                            {{ implode(', ', $campo['opciones'] ?? []) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button wire:click="eliminarCampoPersonalizado({{ $index }})"
                                                class="text-red-600 hover:underline text-xs">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-gray-500">No se han agregado campos.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
        {{-- ===== FIN SERVICIO PERSONALIZADO ===== --}}

        {{-- Buscador de servicio de catálogo --}}
        @if (!$servicio_personalizado)
            <div class="space-y-1">
                <label class="block text-sm text-gray-700">Seleccionar servicio</label>
                <span class="hidden">{{ $forzar_render_servicios }}</span>

                <div class="relative">
                    <input type="text"
                           wire:model="busqueda_servicio"
                           wire:keyup="actualizarSugerenciasServicio"
                           placeholder="Buscar servicio..."
                           autocomplete="off"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <span class="hidden">{{ $forzar_render_servicios }}</span>

                    <ul class="absolute left-0 right-0 z-50 bg-white border rounded shadow mt-1 max-h-48 overflow-auto
                           {{ $mostrar_sugerencias_servicios && count($servicios_sugeridos) ? '' : 'hidden' }}">
                        @foreach ($servicios_sugeridos as $s)
                            <li wire:click="seleccionarServicio({{ $s->id }})"
                                class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer">
                                {{ $s->nombre }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                @error('servicio_seleccionado_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
        @endif

        {{-- Campos personalizados dinámicos (para catálogo) --}}
        @if (!empty($campos_personalizados))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($campos_personalizados as $index => $campo)
                    <div>
                        <label class="block text-sm text-gray-700 mb-1">{{ $campo['nombre'] }}</label>
                        @if ($campo['tipo'] === 'texto')
                            <input type="text" wire:model.defer="campos_personalizados.{{ $index }}.valor"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @elseif ($campo['tipo'] === 'numero')
                            <input type="number" wire:model.defer="campos_personalizados.{{ $index }}.valor"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        @elseif ($campo['tipo'] === 'booleano')
                            <select wire:model.defer="campos_personalizados.{{ $index }}.valor"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">Selecciona</option>
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        @elseif ($campo['tipo'] === 'select')
                            <select wire:model.defer="campos_personalizados.{{ $index }}.valor"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                                <option value="">Selecciona una opción</option>
                                @foreach ($campo['opciones'] as $opcion)
                                    <option value="{{ $opcion }}">{{ $opcion }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Insumos con variantes --}}
        @if (!empty($insumos_con_variantes))
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-700">Selecciona variantes de insumos</h3>
                @foreach ($insumos_con_variantes as $i => $insumo)
                    <div class="border rounded p-4 bg-gray-50">
                        <p class="font-semibold text-sm mb-2 text-gray-800">{{ $insumo['nombre'] }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($insumo['variantes'] as $variante)
                                <label class="flex items-start space-x-2 text-sm text-gray-600">
                                    <input type="checkbox"
                                           wire:model.defer="insumos_con_variantes.{{ $i }}.variantes_seleccionadas"
                                           value="{{ $variante['id'] }}"
                                           class="mt-1 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                                    <span>{{ collect($variante['atributos'])->map(fn($v,$k)=>"$k: $v")->implode(', ') }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Botón para agregar/guardar (SIEMPRE dentro del contenedor) --}}
        <div class="flex justify-end gap-2">
            {{-- principal: agrega o guarda según estado --}}
            <button
                wire:click="{{ ($servicio_personalizado && $modo_editar_personalizado) ? 'guardarServicioPersonalizado' : 'agregarServicio' }}"
                class="px-4 py-2 rounded-md text-sm text-white
               {{ ($servicio_personalizado && $modo_editar_personalizado)
                    ? 'bg-green-600 hover:bg-green-700'
                    : 'bg-blue-600 hover:bg-blue-700' }}">
                {{ ($servicio_personalizado && $modo_editar_personalizado) ? 'Guardar cambios' : '+ Agregar servicio' }}
            </button>

            {{-- secundario: cancelar edición de personalizado --}}
            @if($servicio_personalizado && $modo_editar_personalizado)
                <button
                    type="button"
                    wire:click="resetPersonalizadoUi"
                    class="px-4 py-2 rounded-md text-sm border bg-white hover:bg-gray-100 text-gray-700">
                    Cancelar
                </button>
            @endif
        </div>




        {{-- Tabla de servicios agregados --}}
        @if (!empty($servicios_pedido))
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border border-gray-300 rounded">
                    <thead class="bg-gray-100 text-gray-800">
                    <tr>
                        <th class="px-4 py-2">Servicio</th>
                        <th class="px-4 py-2">Cantidad</th>
                        <th class="px-4 py-2">Precio unitario</th>
                        <th class="px-4 py-2">Subtotal</th>
                        <th class="px-4 py-2">Total final</th>
                        <th class="px-4 py-2">Campos personalizados</th>
                        <th class="px-4 py-2">Insumos usados</th>
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @foreach ($servicios_pedido as $i => $s)
                        <tr class="border-t border-gray-200 align-top">

                            {{-- Servicio --}}
                            <td class="px-4 py-2 font-medium">
                                {{ $s['nombre'] }}
                            </td>

                            {{-- Cantidad --}}
                            <td class="px-4 py-2">
                                <input type="number" min="1" wire:model.lazy="servicios_pedido.{{ $i }}.cantidad"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 text-sm" />
                            </td>

                            {{-- Precio unitario --}}
                            <td class="px-4 py-2 text-sm">
                                ${{ number_format($s['precio_unitario'], 2) }}
                            </td>

                            {{-- Subtotal --}}
                            <td class="px-4 py-2 text-sm font-medium text-gray-800">
                                ${{ number_format(($s['cantidad'] ?? 1) * $s['precio_unitario'], 2) }}
                                @if (!empty($s['total_final']))
                                    <span class="ml-1 text-[11px] text-indigo-600">(overridden)</span>
                                @endif
                            </td>


                            {{-- Total final --}}
                            <td class="px-4 py-2 text-sm text-gray-800 space-y-1">
                                @if (!empty($s['total_final']))
                                    <div>
                                        <strong>${{ number_format($s['total_final'], 2) }}</strong>
                                    </div>
                                    @if (!empty($s['justificacion_total']))
                                        <div class="text-[11px] italic text-gray-500">
                                            Justificación: {{ $s['justificacion_total'] }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs italic">—</span>
                                @endif
                            </td>

                            {{-- Campos personalizados --}}
                            <td class="px-4 py-2 text-xs text-gray-700 space-y-1">
                                @if (!empty($s['campos_personalizados']))
                                    @foreach ($s['campos_personalizados'] as $campo)
                                        <div>
                                            <strong>{{ $campo['nombre'] }}:</strong>
                                            @if (($campo['tipo'] ?? '') === 'booleano')
                                                {{ !empty($campo['valor']) ? 'Sí' : 'No' }}
                                            @elseif (!isset($campo['valor']) || $campo['valor'] === '')
                                                <span class="text-gray-400 italic">—</span>
                                            @else
                                                {{ is_array($campo['valor']) ? implode(', ', $campo['valor']) : $campo['valor'] }}
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 italic">—</span>
                                @endif
                            </td>



                            {{-- Insumos usados --}}
                            <td class="px-4 py-2 text-xs text-blue-700 space-y-1">
                                @if (!empty($s['insumos_usados']))
                                    @foreach ($s['insumos_usados'] as $insumo)
                                        <div class="text-xs text-blue-700 underline">
                                            - {{ $insumo['nombre'] }}
                                            @if (!empty($insumo['atributos']) && is_array($insumo['atributos']))
                                                ({{ collect($insumo['atributos'])->map(fn($v,$k) => "$k: $v")->implode(', ') }})
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 italic">—</span>
                                @endif
                            </td>


                            {{-- Acciones --}}
                            <td class="px-4 py-2 text-sm text-right">
                                <div class="flex flex-col space-y-1 items-end">
                                    <button wire:click="editarServicio({{ $i }})"
                                            class="text-indigo-600 hover:underline">Editar</button>
                                    <button wire:click="eliminarServicio({{ $i }})"
                                            class="text-red-600 hover:underline">Eliminar</button>
                                    @if(($s['tipo'] ?? (is_null($s['servicio_id'] ?? null) ? 'personalizado' : 'catalogo')) === 'personalizado')
                                        <button wire:click="editarEstructuraPersonalizado({{ $i }})"
                                                class="text-indigo-600 hover:underline">Editar estructura</button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>



    {{-- === Sección Totales (F4.3/F4.11) === --}}
    <div wire:key="totals-{{ $totals_refresh }}">
        <div class="bg-white rounded-lg shadow p-6 space-y-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Resumen de pago</h2>

            {{-- Subtotal (solo lectura) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Subtotal (auto)</label>
                <input type="text" value="{{ number_format($subtotal, 2) }}"
                       class="mt-1 w-full rounded-md border-gray-300 bg-gray-100 text-gray-700" readonly>
                <p class="mt-1 text-xs text-gray-500">Suma de cantidades × precio unitario.</p>
            </div>

            {{-- Total final (editable) --}}
            <div>
                <label for="total" class="block text-sm font-medium text-gray-700">Total final *</label>
                <input id="total" type="number" step="0.01" min="0"
                       wire:key="total-input-{{ $totals_refresh }}"
                       wire:model.lazy="total"
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-[#003844] focus:ring-[#003844]">
                @error('total') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Anticipo + Restante --}}
            <div>
                <label for="anticipo" class="block text-sm font-medium text-gray-700">Anticipo</label>
                <input id="anticipo" type="number" step="0.01" min="0"
                       wire:model.lazy="anticipo"
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-[#003844] focus:ring-[#003844]">
                @error('anticipo') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror

                <div class="mt-2">
                    <label class="block text-xs font-medium text-gray-700">Restante (auto)</label>
                    <input type="text" value="{{ number_format(($this->restante ?? 0), 2) }}"
                           class="mt-1 w-full rounded-md border-gray-300 bg-gray-100 text-gray-700" readonly>
                </div>
            </div>

            {{-- === Comprobante de pago === --}}
            <div class="pt-2">
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-medium text-slate-800 text-sm sm:text-base">Comprobante de pago</h4>
                        <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                            PDF / CDR • máx 100MB
                        </span>
                    </div>

                    {{-- Lista de comprobantes ya guardados --}}
                    @if (!empty($comprobantes_pedido) && $comprobantes_pedido->count())
                        <div class="mt-3 space-y-1">
                            @foreach ($comprobantes_pedido as $c)
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4M7 12a5 5 0 1010 0 5 5 0 00-10 0z"/>
                                      </svg>
                                      {{ $c->original_name }}
                                    </span>

                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('docs.pedido.descargar', $c->id) }}"
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-700 text-xs underline">
                                            Descargar
                                        </a>

                                        <button type="button"
                                                wire:click="prepararReemplazoComprobante({{ $c->id }})"
                                                class="text-slate-700 hover:text-slate-900 text-xs underline">
                                            Reemplazar
                                        </button>

                                        <button type="button"
                                                wire:click="eliminarComprobantePedido({{ $c->id }})"
                                                class="text-red-600 hover:text-red-700 text-xs underline">
                                            Quitar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Zona de carga: drag/click + botón subir --}}
                    <div class="mt-4">
                        <label for="comprobante_pago_input" class="block">
                            <div class="rounded-xl border-2 border-dashed border-slate-300 hover:border-slate-400 bg-white text-center px-4 py-8 cursor-pointer transition">
                                <div class="mx-auto w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0l-3.5 3.5M12 4l3.5 3.5"/>
                                    </svg>
                                </div>
                                <p class="mt-3 text-sm text-slate-700">
                                    Arrastra y suelta el archivo aquí o
                                    <span class="text-blue-700 underline">haz clic para buscar</span>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">Acepta .pdf y .cdr</p>
                            </div>
                        </label>

                        {{-- input de archivo --}}
                        <input
                            id="comprobante_pago_input"
                            type="file"
                            wire:model="archivo_comprobante"
                            accept=".pdf,.cdr,application/pdf,application/vnd.corel-draw"
                            class="hidden"
                        />

                        {{-- Estado de selección + acciones --}}
                        @if ($archivo_comprobante)
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-2 text-xs sm:text-sm bg-green-50 text-green-700 px-3 py-1.5 rounded-full border border-green-200">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4 4m0 0l4-4m-4 4V4"/>
                                    </svg>
                                    {{ $archivo_comprobante->getClientOriginalName() }}
                                </span>

                                <button type="button"
                                        wire:click="subirComprobantePedido"
                                        class="px-3 py-1.5 rounded-lg text-white bg-[#003844] hover:bg-[#002f39] text-xs">
                                    Subir comprobante
                                </button>

                                <button type="button"
                                        wire:click="$set('archivo_comprobante', null)"
                                        class="text-red-600 hover:text-red-700 text-xs underline">
                                    Quitar
                                </button>
                            </div>
                        @endif

                        {{-- Progreso de carga --}}
                        <div wire:loading wire:target="archivo_comprobante" class="mt-3">
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-2 bg-slate-700 animate-pulse" style="width: 60%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Subiendo…</p>
                        </div>

                        {{-- Errores del comprobante --}}
                        @error('archivo_comprobante')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Listener para abrir el file-picker cuando se elige "Reemplazar" --}}
            <script>
                document.addEventListener('abrir-input-comprobante', () => {
                    const input = document.getElementById('comprobante_pago_input');
                    if (input) input.click();
                });
            </script>



        </div>
    </div>





    {{-- Botones --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('pedidos') }}"
           class="px-4 py-2 rounded-md border bg-white text-gray-700 hover:bg-gray-100 text-sm">
            Cancelar
        </a>
        <button wire:click="guardar"
                class="px-4 py-2 rounded-md bg-[#003844] hover:bg-[#002f39] text-white text-sm">
            Guardar pedido
        </button>
    </div>

    {{-- Modal de edición de servicio --}}
    @if ($modal_servicio_abierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white w-full max-w-3xl mx-4 rounded-lg shadow-lg p-6 relative z-50 overflow-y-auto max-h-[90vh]">

                {{-- Encabezado --}}
                <h2 class="text-lg font-semibold mb-4 text-gray-800">
                    @if(
                        isset($servicios_pedido[$indice_edicion_servicio]['tipo'])
                            ? $servicios_pedido[$indice_edicion_servicio]['tipo'] === 'catalogo'
                            : !is_null($servicios_pedido[$indice_edicion_servicio]['servicio_id'] ?? null)
                    )
                        Editar servicio:
                        {{ $servicios_catalogo->find($servicio_seleccionado_id)->nombre
                            ?? ($servicios_pedido[$indice_edicion_servicio]['nombre'] ?? '') }}
                    @else
                        Editar servicio:
                        {{ $servicios_pedido[$indice_edicion_servicio]['nombre'] ?? 'Servicio personalizado' }}
                    @endif
                </h2>


                {{-- Cantidad --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                    <input type="number" min="1" wire:model.lazy="servicios_pedido.{{ $indice_edicion_servicio }}.cantidad"
                           class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                {{-- Precio unitario (bloqueado) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Precio unitario</label>
                    <input type="number" step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-700"
                           wire:model="servicios_pedido.{{ $indice_edicion_servicio }}.precio_unitario"
                           readonly>
                    <p class="text-xs text-gray-500 mt-1">Precio del catálogo. Usa “Total final” para descuentos/cargos.</p>
                </div>

                {{-- Subtotal (auto) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Subtotal (auto)</label>
                    <input type="text"
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-700"
                           value="{{ number_format($servicios_pedido[$indice_edicion_servicio]['subtotal'] ?? 0, 2) }}"
                           readonly>
                    <p class="text-xs text-gray-500 mt-1">Cantidad × Precio unitario.</p>
                </div>

                {{-- Total final y justificación --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Total final (opcional)</label>
                    <input type="number" step="0.01" wire:model.lazy="modal_total_final"
                           class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                @if (!is_null($modal_total_final) && $modal_total_final !== '')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Justificación del total</label>
                        <textarea wire:model.lazy="modal_justificacion_total"
                                  class="w-full mt-1 border-gray-300 rounded-md shadow-sm"></textarea>
                        @error('modal_justificacion_total')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif


                {{-- Archivo de diseño --}}
                <div class="mb-6">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5">
                        <div class="flex items-center justify-between">
                            <h4 class="font-medium text-slate-800 text-sm sm:text-base">Archivo de diseño</h4>
                            <span class="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">
                                PDF / CDR • máx 100MB
                            </span>
                        </div>

                        {{-- Estado actual --}}
                        <div class="mt-3 space-y-2">
                            @if ($archivo_diseno)
                                {{-- Seleccionado (aún no subido) --}}
                                <div class="flex flex-wrap items-center gap-2">
                                      <span class="inline-flex items-center gap-2 text-xs sm:text-sm bg-green-50 text-green-700 px-3 py-1.5 rounded-full border border-green-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4 4m0 0l4-4m-4 4V4"/>
                                        </svg>
                                        {{ $archivo_diseno->getClientOriginalName() }}
                                      </span>

                                    <button type="button"
                                            wire:click="eliminarArchivo"
                                            class="text-red-600 hover:text-red-700 text-xs underline">
                                        Quitar
                                    </button>
                                </div>

                            @elseif (!empty($docvar_actual))
                                {{-- Ya guardado en BD (variante existente) --}}
                                <div class="flex flex-wrap items-center gap-3">
                                  <span class="inline-flex items-center gap-2 text-xs sm:text-sm bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full border border-emerald-200">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4M7 12a5 5 0 1010 0 5 5 0 00-10 0z"/>
                                    </svg>
                                    {{ $docvar_actual->original_name ?? ($archivo_diseno_nombre ?? 'archivo') }}
                                  </span>

                                    <a href="{{ route('docs.variante.descargar', $docvar_actual->id) }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-700 text-xs underline">
                                        Descargar
                                    </a>

                                    <button type="button"
                                            wire:click="prepararReemplazoDiseno({{ $servicios_pedido[$indice_edicion_servicio]['psv_id'] ?? 0 }})"
                                            class="text-slate-700 hover:text-slate-900 text-xs underline">
                                        Reemplazar
                                    </button>

                                    <button type="button"
                                            wire:click="eliminarDisenoActual"
                                            class="text-red-600 hover:text-red-700 text-xs underline">
                                        Quitar
                                    </button>
                                </div>

                            @elseif (!empty($archivo_diseno_nombre))
                                {{-- Sólo nombre previo (no hay variante todavía) --}}
                                <div class="flex flex-wrap items-center gap-2">
                                  <span class="inline-flex items-center gap-2 text-xs sm:text-sm bg-amber-50 text-amber-700 px-3 py-1.5 rounded-full border border-amber-200">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ $archivo_diseno_nombre }}
                                  </span>

                                    <label for="archivo_diseno_input"
                                           class="text-slate-700 hover:text-slate-900 text-xs underline cursor-pointer">
                                        Subir ahora
                                    </label>

                                    <button type="button"
                                            wire:click="eliminarArchivo"
                                            class="text-red-600 hover:text-red-700 text-xs underline">
                                        Quitar
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Zona de carga: drop/selección --}}
                        <label for="archivo_diseno_input" class="mt-4 block w-full">
                            <div class="w-full rounded-xl border-2 border-dashed border-slate-300 hover:border-slate-400 bg-white text-center px-4 py-8 transition cursor-pointer">
                                <div class="mx-auto w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0l-3.5 3.5M12 4l3.5 3.5"/>
                                    </svg>
                                </div>
                                <p class="mt-3 text-sm text-slate-700">
                                    Arrastra y suelta el archivo aquí o
                                    <span class="text-blue-700 underline">haz clic para buscar</span>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">Acepta .pdf y .cdr</p>
                            </div>
                        </label>

                        {{-- Input oculto: sube de inmediato si la variante ya existe (psv_id > 0) --}}
                        <input
                            id="archivo_diseno_input"
                            type="file"
                            class="hidden"
                            accept=".pdf,.cdr,application/pdf,application/vnd.corel-draw"
                            wire:model="archivo_diseno"
                            wire:change="subirDisenoVariante({{ $servicios_pedido[$indice_edicion_servicio]['psv_id'] ?? 0 }})"
                        />

                        {{-- Progreso de carga Livewire --}}
                        <div wire:loading wire:target="archivo_diseno" class="mt-3">
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-2 bg-slate-700 animate-pulse" style="width: 60%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Subiendo…</p>
                        </div>

                        @error('archivo_diseno')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror

                        {{-- Aplicar a todas las variantes --}}
                        @if (!empty($servicios_pedido[$indice_edicion_servicio]['servicio_id'] ?? null))
                            <div class="mt-4 border-t border-slate-200 pt-4">
                                <label class="flex items-start gap-3 text-sm text-slate-700">
                                    <input type="checkbox" wire:model.defer="aplicar_a_todas"
                                           class="mt-0.5 rounded border-slate-300">
                                    <span>
                                        Aplicar este archivo a <strong>todas las variantes</strong> de este servicio en el pedido.
                                        <span class="block text-xs text-slate-500 mt-0.5">
                                          Se adjuntará el mismo documento a cada variante al guardar.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- JS mínimo para acciones --}}
                <script>
                    document.addEventListener('abrir-input-diseno', () => {
                        document.getElementById('archivo_diseno_input')?.click();
                    });
                    document.addEventListener('abrir-url', (e) => {
                        if (e?.detail?.url) window.open(e.detail.url, '_blank');
                    });
                </script>




                {{-- Campos personalizados --}}
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Campos personalizados</h3>
                    @foreach ($campos_personalizados as $index => $campo)
                        <div class="mb-2">
                            <label class="block text-sm text-gray-700 font-medium">{{ $campo['nombre'] }}</label>
                            @switch($campo['tipo'])
                                @case('texto')
                                    <input type="text" wire:model.lazy="campos_personalizados.{{ $index }}.valor"
                                           class="w-full border-gray-300 rounded-md shadow-sm" />
                                    @break

                                @case('numero')
                                    <input type="number" wire:model.lazy="campos_personalizados.{{ $index }}.valor"
                                           class="w-full border-gray-300 rounded-md shadow-sm" />
                                    @break

                                @case('booleano')
                                    <select wire:model.lazy="campos_personalizados.{{ $index }}.valor"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Selecciona</option>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                    @break

                                @case('select')
                                    <select wire:model.lazy="campos_personalizados.{{ $index }}.valor"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Selecciona</option>
                                        @foreach ($campo['opciones'] as $op)
                                            <option value="{{ $op }}">{{ $op }}</option>
                                        @endforeach
                                    </select>
                                    @break
                            @endswitch
                        </div>
                    @endforeach
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-4 mt-6">
                    <button wire:click="resetServicio"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-sm text-gray-800">
                        Cancelar
                    </button>
                    <button wire:click="guardarEdicionServicio"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-sm text-white">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
