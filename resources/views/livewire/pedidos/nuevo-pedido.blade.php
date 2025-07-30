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

</div>
