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

        {{-- Selección de servicio --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Seleccionar servicio</label>
                <select wire:model="servicio_seleccionado_id" wire:change="cargarServicioSeleccionado"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">Selecciona uno</option>
                    @foreach ($servicios_catalogo as $servicio)
                        <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                    @endforeach
                </select>
                @error('servicio_seleccionado_id') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Campos personalizados dinámicos --}}
        @if(!empty($campos_personalizados))
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
            <div class="mt-6 space-y-4">
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
                                    <span>
                                {{ collect($variante['atributos'])->map(fn($v, $k) => "$k: $v")->implode(', ') }}
                            </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif



        {{-- Botón para agregar servicio --}}
        <div class="flex justify-end">
            <button wire:click="agregarServicio"
                    class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">
                + Agregar servicio
            </button>
        </div>

        {{-- Tabla de servicios agregados --}}
        @if (!empty($servicios_pedido))
            <div class="mt-6 overflow-auto">
                <table class="w-full text-sm text-left border border-gray-300 rounded">
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
                                @foreach ($s['campos_personalizados'] as $campo)
                                    @if (!empty($campo['valor']))
                                        <div>
                                            <strong>{{ $campo['nombre'] }}:</strong>
                                            @if ($campo['tipo'] === 'booleano')
                                                {{ $campo['valor'] ? 'Sí' : 'No' }}
                                            @else
                                                {{ $campo['valor'] }}
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </td>

                            {{-- Insumos usados --}}
                            <td class="px-4 py-2 text-xs text-blue-700 space-y-1">
                                @if (!empty($s['insumos_usados']))
                                    @foreach ($s['insumos_usados'] as $insumo)
                                        <div class="text-xs text-blue-700 underline">
                                            - {{ $insumo['nombre'] }}
                                            @if (!empty($insumo['atributos']) && is_array($insumo['atributos']))
                                                ({{ collect($insumo['atributos'])->map(fn($v, $k) => "$k: $v")->implode(', ') }})
                                            @endif
                                        </div>
                                    @endforeach


                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-2 space-y-1">
                                <button wire:click="editarServicio({{ $i }})"
                                        class="text-blue-600 hover:underline text-sm block">Editar</button>

                                <button wire:click="eliminarServicio({{ $i }})"
                                        class="text-red-600 hover:underline text-sm block">Eliminar</button>
                            </td>


                        </tr>
                    @endforeach
                    </tbody>


                </table>
            </div>
        @endif
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
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center">
            <div class="bg-white w-full max-w-3xl mx-4 rounded-lg shadow-lg p-6 relative z-50 overflow-y-auto max-h-[90vh]">

                {{-- Encabezado --}}
                <h2 class="text-lg font-semibold mb-4 text-gray-800">
                    Editar servicio: {{ $servicios_catalogo->find($servicio_seleccionado_id)->nombre ?? '' }}
                </h2>

                {{-- Cantidad --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                    <input type="number" min="1" wire:model.lazy="servicios_pedido.{{ $indice_edicion_servicio }}.cantidad"
                           class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                {{-- Precio unitario --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Precio unitario</label>
                    <input type="number" min="0" step="0.01" wire:model.lazy="servicios_pedido.{{ $indice_edicion_servicio }}.precio_unitario"
                           class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                {{-- Total final y justificación --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Total final (opcional)</label>
                    <input type="number" step="0.01" wire:model.lazy="servicios_pedido.{{ $indice_edicion_servicio }}.total_final"
                           class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                @if (!empty($servicios_pedido[$indice_edicion_servicio]['total_final']))
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Justificación del total</label>
                        <textarea wire:model.lazy="servicios_pedido.{{ $indice_edicion_servicio }}.justificacion_total"
                                  class="w-full mt-1 border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                @endif

                {{-- Archivo de diseño --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Archivo de diseño (opcional)</label>

                    @if ($archivo_diseno)
                        <div class="flex items-center gap-2 mt-2">
                        <span class="text-sm text-green-700">
                            Archivo seleccionado: <strong>{{ $archivo_diseno->getClientOriginalName() }}</strong>
                        </span>
                            <button type="button" wire:click="eliminarArchivo"
                                    class="text-red-600 text-xs hover:underline">Eliminar</button>
                        </div>
                    @elseif ($archivo_diseno_nombre)
                        <div class="flex items-center gap-2 mt-2">
                        <span class="text-sm text-green-700">
                            Archivo previamente guardado: <strong>{{ $archivo_diseno_nombre }}</strong>
                        </span>
                            <button type="button" wire:click="eliminarArchivo"
                                    class="text-red-600 text-xs hover:underline">Eliminar</button>
                        </div>
                    @endif

                    <input type="file" wire:model="archivo_diseno"
                           class="mt-2 block w-full text-sm text-gray-900 border border-gray-300 rounded cursor-pointer bg-white" />

                    @error('archivo_diseno')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

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
