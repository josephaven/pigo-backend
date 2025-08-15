<?php

namespace App\Livewire\Pedidos;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\MetodoPago;
use App\Models\FacturaPedido;
use App\Models\Servicio;
use App\Models\CampoPersonalizado;
use App\Models\Insumo;
use App\Models\VarianteInsumo;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use App\Models\PedidoServicioVariante;
use App\Models\PedidoInsumo;
use App\Models\RespuestaCampoPedido;
use Illuminate\Validation\Validator;
use App\Services\DocumentoService;
use App\Models\ComprobantePedido;
use App\Models\ComprobanteVariante;
use Illuminate\Support\Facades\Storage;
use App\Services\InventoryConsumptionService;




class NuevoPedido extends Component
{
    use WithFileUploads;
    public $pedido_id;
    public $cliente_id;
    public $sucursal_registro_id;
    public $sucursal_entrega_id;
    public $sucursal_elaboracion_id;
    public $fecha_entrega;
    public $anticipo = 0;
    public $total = 0;
    public $justificacion_precio = '';

    public $modo_edicion = false;

    // Cliente nuevo
    public $cliente_nuevo = false;
    public $nombre_cliente;
    public $telefono_cliente;
    public $tipo_cliente = 'Normal';
    public $ocupacion_cliente;
    public $fecha_nacimiento;

    // Buscador tipo Google
    public $busqueda_cliente = '';
    public $clientes_sugeridos = [];
    public $mostrar_sugerencias = false;
    public $forzar_render = 0;
    public $cliente_seleccionado;

    // Método de pago (para el pedido)
    public $metodo_pago_id;

    // Requiere factura
    public $requiere_factura = false;

    // Datos fiscales
    public $rfc, $razon_social, $direccion_fiscal, $uso_cfdi, $metodo_pago_factura;

    public $metodos_pago = [];

    public $servicios_catalogo = [];
    public $servicios_pedido = [];

// Auxiliares para cargar dinámicamente
    public $servicio_seleccionado_id;
    public $campos_personalizados = [];
    public $insumos_con_variantes = [];

    public $modal_servicio_abierto = false;
    public $indice_edicion_servicio = null;
    public $archivo_diseno; // para archivo de diseño (Livewire upload futuro)
    public $archivo_diseno_nombre;

    public $busqueda_servicio = '';
    public $servicios_sugeridos = [];
    public $mostrar_sugerencias_servicios = false;
    public $forzar_render_servicios = 0;

    public $servicio_personalizado = false;
    public $servicio_personalizado_nombre;
    public $servicio_personalizado_precio;
    public $servicio_personalizado_descripcion;


    public $usar_campos_personalizados = false;
    public $busqueda_insumo = '';
    public $insumos_sugeridos = [];

    public $insumos_agregados = [];
    public $cantidad_insumo = 1;
    public $unidad_insumo = '';
    public $mostrar_sugerencias_insumo = false;
    public $forzar_render_insumo;
    public $insumo_seleccionado;

    // Para campos personalizados manuales
    public $nuevo_nombre_campo, $nuevo_tipo_campo = 'texto', $nuevo_opciones_campo;
    public $nuevo_requerido_campo = false, $nuevo_activo_campo = true;

    public $nuevoCampo = [
        'nombre' => '',
        'tipo' => '',
        'opciones' => '',
    ];

    public $campos_personalizados_temporales = [];

    public $modo_editar_personalizado = false;
    public $indice_edicion_personalizado = null;
    public $nombre_original_personalizado = null;

    public $insumo_id = null;

    public $modal_total_final = null;
    public $modal_justificacion_total = null;

    public int $totals_refresh = 0;

    public $comprobantes_pedido = [];

    public bool $aplicar_a_todas = false;

    public bool $total_tocado_manualmente = false;

    public $docvar_actual = null;
    public $archivo_comprobante = null;   // ⬅️ solo comprobantes del pedido

    public $archivo_diseno_masivo = null; // ⬅️ DISEÑO para aplicar a TODAS las variantes de un servicio

    public ?int $reemplazar_comprobante_id = null; // id del comprobante a reemplazar (opcional)


    public ?int $reemplazar_diseno_psv_id = null;
    public $modal_variante_id = null;
    public $modal_variante_label = null;





    public function mount($id = null)
    {
        $this->sucursal_registro_id = Auth::user()->sucursal_id;
        $this->fecha_entrega = now()->addDays(2)->format('Y-m-d');
        $this->servicios_catalogo = Servicio::where('activo', true)->get();
        $this->metodos_pago = MetodoPago::all();

        if ($id) {
            $this->modo_edicion = true;
            $this->cargarPedido($id);
        }
        $this->refrescarComprobantes();

    }

    public function render()
    {
        return view('livewire.pedidos.nuevo-pedido', [
            'sucursales' => Sucursal::all(),
            'subtotal'   => $this->calcularSubtotalServicios(),
        ])->layout('layouts.app');
    }


    protected function rules()
    {
        $rules = [
            // Base del pedido
            'sucursal_entrega_id'      => ['required','exists:sucursales,id'],
            'sucursal_elaboracion_id'  => ['required','exists:sucursales,id'],
            'fecha_entrega'            => ['required','date','after_or_equal:today'],
            'total'                    => ['required','numeric','min:0'],
            'anticipo'                 => ['required','numeric','min:0'],
            'justificacion_precio'     => ['nullable','string','max:500'],
            'metodo_pago_id'           => ['required','exists:metodo_pagos,id'],
        ];

        // ✅ Solo exigir cliente_id cuando NO es cliente nuevo
        if (!$this->cliente_nuevo) {
            $rules['cliente_id'] = ['required','exists:clientes,id'];
        }

        // Si requiere factura, valida los campos fiscales
        if ($this->requiere_factura) {
            $rules = array_merge($rules, [
                'rfc'                 => ['required','string','max:13'],
                'razon_social'        => ['required','string','max:255'],
                'direccion_fiscal'    => ['required','string','max:255'],
                'uso_cfdi'            => ['nullable','string','max:50'],
                'metodo_pago_factura' => ['nullable','string','max:100'],
            ]);
        }

        // Si es cliente nuevo, valida datos del cliente
        if ($this->cliente_nuevo) {
            $rules = array_merge($rules, [
                'nombre_cliente'    => ['required','string','max:255'],
                'telefono_cliente'  => ['required', Rule::unique('clientes', 'telefono')],
                'tipo_cliente'      => ['required','in:Normal,Frecuente,Maquilador'],
                'ocupacion_cliente' => ['nullable','string','max:255'],
                'fecha_nacimiento'  => ['nullable','date','before:today'],
            ]);
        }


        return $rules;
    }



    public function messages()
    {
        return [
            // Pedido
            'cliente_id.required' => 'Selecciona un cliente.',
            'fecha_entrega.after_or_equal' => 'La fecha de entrega no puede ser anterior a hoy.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser numérico.',
            'total.min' => 'El total no puede ser negativo.',
            'anticipo.required' => 'El anticipo es obligatorio.',
            'anticipo.numeric' => 'El anticipo debe ser numérico.',
            'anticipo.min' => 'El anticipo no puede ser negativo.',
            'justificacion_precio.max' => 'La justificación es demasiado larga.',

            // Cliente nuevo
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
            'telefono_cliente.required' => 'El teléfono es obligatorio.',
            'telefono_cliente.unique' => 'Este teléfono ya existe en tus clientes.',
            'tipo_cliente.required' => 'El tipo de cliente es obligatorio.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',

            // Factura
            'rfc.required' => 'El RFC es obligatorio.',
            'razon_social.required' => 'La razón social es obligatoria.',
            'direccion_fiscal.required' => 'La dirección fiscal es obligatoria.',


            // opcional:
            'archivo_comprobante.max'   => 'Máx 100 MB.',
            'archivo_comprobante.mimetypes' => 'Solo PDF o CDR.',
            'archivo_diseno.max'        => 'Máx 100 MB.',
            'archivo_diseno.mimetypes'  => 'Solo PDF o CDR.',
            'archivo_diseno_masivo.max' => 'Máx 100 MB.',
            'archivo_diseno_masivo.mimetypes' => 'Solo PDF o CDR.',


        ];
    }

    public function attributes()
    {
        return [
            'cliente_id' => 'cliente',
            'sucursal_entrega_id' => 'sucursal de entrega',
            'sucursal_elaboracion_id' => 'sucursal de elaboración',
            'fecha_entrega' => 'fecha de entrega',
            'metodo_pago_id' => 'método de pago',
            'justificacion_precio' => 'justificación',
            'telefono_cliente' => 'teléfono',
            'razon_social' => 'razón social',
            'direccion_fiscal' => 'dirección fiscal',
        ];
    }


    public function updated($prop)
    {
        \Log::info('Busqueda actual:', ['q' => $this->busqueda_cliente]);
        if ($prop === 'busqueda_cliente') {
            $this->forzar_render++;
            if (strlen($this->busqueda_cliente) < 2) {
                $this->clientes_sugeridos = [];
                $this->mostrar_sugerencias = false;
                return;
            }

            $this->clientes_sugeridos = Cliente::where(function ($query) {
                $query->where('nombre_completo', 'ILIKE', '%' . $this->busqueda_cliente . '%')
                    ->orWhere('telefono', 'ILIKE', '%' . $this->busqueda_cliente . '%');
            })->limit(5)->get();

            $this->mostrar_sugerencias = true;
        }

        if ($prop === 'busqueda_servicio') {
            $this->forzar_render_servicios++;

            if (strlen($this->busqueda_servicio) < 2) {
                $this->servicios_sugeridos = [];
                $this->mostrar_sugerencias_servicios = false;
                return;
            }

            $this->servicios_sugeridos = Servicio::where('activo', true)
                ->where('nombre', 'ILIKE', '%' . $this->busqueda_servicio . '%')
                ->limit(5)
                ->get();

            $this->mostrar_sugerencias_servicios = true;
        }

    }

    public function seleccionarCliente($id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->busqueda_cliente = $cliente->nombre_completo;
            $this->mostrar_sugerencias = false;
            $this->cliente_seleccionado = $cliente;

            // Cargar última factura previa
            $ultimaFactura = FacturaPedido::whereHas('pedido', function ($q) use ($cliente) {
                $q->where('cliente_id', $cliente->id);
            })->latest()->first();

            if ($ultimaFactura) {
                $this->razon_social = $ultimaFactura->razon_social;
                $this->rfc = $ultimaFactura->rfc;
                $this->direccion_fiscal = $ultimaFactura->direccion;
                $this->uso_cfdi = $ultimaFactura->uso_cfdi;
                $this->metodo_pago_factura = $ultimaFactura->metodo_pago;
            } else {
                $this->razon_social = $this->rfc = $this->direccion_fiscal = $this->uso_cfdi = $this->metodo_pago_factura = '';
            }
        }
    }

    public function actualizarSugerencias()
    {
        if (strlen($this->busqueda_cliente) < 2) {
            $this->clientes_sugeridos = [];
            $this->mostrar_sugerencias = false;
            return;
        }

        $this->clientes_sugeridos = Cliente::where(function ($query) {
            $query->where('nombre_completo', 'ILIKE', '%' . $this->busqueda_cliente . '%')
                ->orWhere('telefono', 'ILIKE', '%' . $this->busqueda_cliente . '%');
        })->limit(5)->get();

        $this->mostrar_sugerencias = true;
    }


    public function updatedClienteNuevo($valor)
    {
        if ($valor) {
            $this->reset([
                'cliente_id',
                'busqueda_cliente',
                'clientes_sugeridos',
                'mostrar_sugerencias',
            ]);
        }
    }


    public function seleccionarServicio($id)
    {
        $servicio = Servicio::find($id);

        if ($servicio) {
            $this->servicio_seleccionado_id = $servicio->id;
            $this->busqueda_servicio = $servicio->nombre;
            $this->mostrar_sugerencias_servicios = false;
            $this->cargarServicioSeleccionado();
        }
    }

    public function actualizarSugerenciasServicio()
    {
        $this->forzar_render_servicios++;

        if (strlen($this->busqueda_servicio) < 2) {
            $this->servicios_sugeridos = [];
            $this->mostrar_sugerencias_servicios = false;
            return;
        }

        $this->servicios_sugeridos = Servicio::where('activo', true)
            ->where('nombre', 'ILIKE', '%' . $this->busqueda_servicio . '%')
            ->limit(5)
            ->get();

        $this->mostrar_sugerencias_servicios = true;
    }


    public function cargarPedido($id)
    {
        // 1) Trae lo necesario del pedido
        $pedido = Pedido::with([
            'cliente',
            'factura',
            'variantes.servicio',
            'variantes.insumos.insumo.categoria',
            'variantes.respuestasCampos.campo',
        ])->findOrFail($id);

        // 2) Generales
        $this->pedido_id               = $pedido->id;
        $this->cliente_id              = $pedido->cliente_id;
        $this->cliente_seleccionado    = $pedido->cliente;
        $this->busqueda_cliente        = optional($pedido->cliente)->nombre_completo ?? '';

        $this->sucursal_entrega_id     = $pedido->sucursal_entrega_id;
        $this->sucursal_elaboracion_id = $pedido->sucursal_elaboracion_id;
        $this->fecha_entrega           = optional($pedido->fecha_entrega)->toDateString();
        $this->anticipo                = $pedido->anticipo;
        $this->total                   = $pedido->total;
        $this->justificacion_precio    = $pedido->justificacion_precio;
        $this->metodo_pago_id          = $pedido->metodo_pago_id;

        // 3) Factura
        if ($pedido->factura) {
            $this->requiere_factura     = true;
            $this->rfc                  = $pedido->factura->rfc;
            $this->razon_social         = $pedido->factura->razon_social;
            $this->direccion_fiscal     = $pedido->factura->direccion;
            $this->uso_cfdi             = $pedido->factura->uso_cfdi;
            $this->metodo_pago_factura  = $pedido->factura->metodo_pago;
        } else {
            $this->requiere_factura = false;
            $this->rfc = $this->razon_social = $this->direccion_fiscal = $this->uso_cfdi = $this->metodo_pago_factura = '';
        }

        // 4) Pre-carga en bloque de variantes (para evitar N+1)
        $variantIds = [];
        foreach ($pedido->variantes as $row) {
            foreach ($row->insumos as $pi) {
                if (!empty($pi->variante_id)) {
                    $variantIds[] = (int)$pi->variante_id;
                }
            }
        }
        $variantIds = array_values(array_unique($variantIds));

        $variantesMap = collect();
        if (!empty($variantIds)) {
            $variantesMap = \App\Models\VarianteInsumo::with(['insumo'])
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');
        }


        // 5) Reconstruir filas (1 fila = 1 variante)
        $this->servicios_pedido = collect($pedido->variantes)->map(function ($row) use ($variantesMap) {
            $esPersonalizado = is_null($row->servicio_id);

            // Atributos de la variante (para personalizados)
            $attrs = null;
            if (!empty($row->atributos)) {
                $tmp = is_string($row->atributos) ? json_decode($row->atributos, true) : $row->atributos;
                if (is_array($tmp)) $attrs = $tmp;
            }

            // Nombre visible del servicio
            $nombre = $esPersonalizado
                ? ($row->nombre_personalizado ?? 'Servicio personalizado')
                : optional($row->servicio)->nombre;

            // Campos personalizados
            if ($esPersonalizado) {
                $campos     = $attrs['campos_personalizados'] ?? [];
                $campos_def = $attrs['campos_def'] ?? [];
            } else {
                $campos = collect($row->respuestasCampos ?? [])->map(function ($r) {
                    return [
                        'id'       => $r->campo_personalizado_id ?? null,
                        'nombre'   => optional($r->campo)->nombre ?? 'Campo',
                        'tipo'     => optional($r->campo)->tipo ?? 'texto',
                        'valor'    => $r->valor ?? null,
                        'opciones' => optional($r->campo)->opciones ?? [],
                    ];
                })->values()->toArray();
                $campos_def = [];
            }

            // Insumos usados (pedido_insumo)
            $insumos = collect($row->insumos ?? [])->map(function ($pi) {
                $attrsPi = $pi->atributos ?? null;
                if (is_string($attrsPi)) {
                    $attrsPi = json_decode($attrsPi, true);
                }
                $attrsPi = $this->atributosAsociativos($attrsPi);

                return [
                    'insumo_id'   => $pi->insumo_id ?? null,
                    'id'          => $pi->insumo_id ?? null,
                    'nombre'      => optional($pi->insumo)->nombre ?? '—',
                    'categoria'   => optional(optional($pi->insumo)->categoria)->nombre ?? '—',
                    'cantidad'    => (float)($pi->cantidad ?? 0),
                    'unidad'      => $pi->unidad ?? (optional($pi->insumo)->unidad_medida ?? ''),
                    'variante_id' => $pi->variante_id ?? null,
                    'atributos'   => $attrsPi,
                ];
            })->values()->toArray();

            // Determinar la variante principal de la fila (la primera con variante_id)
            $insumoVar      = collect($insumos)->first(fn($x) => !empty($x['variante_id']));
            $varianteId     = $insumoVar['variante_id'] ?? null;
            $varianteLabel  = null;

            if ($varianteId && $variantesMap->has($varianteId)) {
                $var = $variantesMap->get($varianteId);
                $varianteLabel = $this->formatearLabelVariante($var);
            }

            return [
                'psv_id'                => $row->id,
                'tipo'                  => $esPersonalizado ? 'personalizado' : 'catalogo',
                'servicio_id'           => $row->servicio_id, // null si es personalizado
                'nombre'                => $nombre,
                'descripcion'           => $row->descripcion,
                'campos_personalizados' => $campos,
                'campos_def'            => $campos_def,   // para reconstruir builder en personalizados
                'insumos_usados'        => $insumos,

                'cantidad'              => (int) $row->cantidad,
                'precio_unitario'       => (float) $row->precio_unitario,
                'subtotal'              => (float) $row->subtotal,
                'total_final'           => $row->total_final ?? null,
                'justificacion_total'   => $row->justificacion_total ?? null,

                'archivo_diseno'        => null, // se carga al abrir modal si hace falta
                'archivo_diseno_nombre' => $row->nota_disenio,

                // 👇 identidad de la variante (para mostrar/editar por separado)
                'variante_id'           => $varianteId,
                'variante_label'        => $varianteLabel,
            ];
        })->toArray();

        // 6) Limpieza de helpers de selección
        $this->servicio_seleccionado_id   = null;
        $this->campos_personalizados      = [];
        $this->insumos_con_variantes      = [];
        $this->busqueda_servicio          = '';
        $this->servicio_personalizado     = false;
        $this->usar_campos_personalizados = false;

        // En edición, no recalcules total automáticamente al mover sliders en UI
        $this->total_tocado_manualmente = true;

        // Refresca chips de comprobantes del pedido
        $this->refrescarComprobantes();
    }


    public function guardar()
    {
        // 0) Al menos una línea/variante
        if (count($this->servicios_pedido) === 0) {
            $this->addError('servicios_pedido', 'Debes agregar al menos un servicio al pedido.');
            return;
        }

        // 1) Validación
        $this->validate();

        // 2) Alta de cliente nuevo (si aplica)
        if ($this->cliente_nuevo) {
            $nuevo = Cliente::create([
                'nombre_completo'  => $this->nombre_cliente,
                'telefono'         => $this->telefono_cliente,
                'tipo_cliente'     => $this->tipo_cliente,
                'ocupacion'        => $this->ocupacion_cliente,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sucursal_id'      => $this->sucursal_registro_id,
            ]);
            $this->cliente_id    = $nuevo->id;
            $this->cliente_nuevo = false;
            $this->toastJs('success', 'Cliente nuevo registrado correctamente');
        }

        // Variables para ajustes en EDICIÓN
        $consumoAntes = collect();
        $oldSucursal  = null;

        // 3) Persistencia (crear o reconstruir)
        DB::transaction(function () use (&$consumoAntes, &$oldSucursal) {
            $data = [
                'cliente_id'              => $this->cliente_id,
                'sucursal_registro_id'    => $this->sucursal_registro_id,
                'sucursal_entrega_id'     => $this->sucursal_entrega_id,
                'sucursal_elaboracion_id' => $this->sucursal_elaboracion_id,
                'fecha_entrega'           => $this->fecha_entrega,
                'anticipo'                => $this->anticipo,
                'total'                   => $this->total,
                'justificacion_precio'    => $this->justificacion_precio,
                'metodo_pago_id'          => $this->metodo_pago_id,
                'user_id'                 => Auth::id(),
            ];

            $docs = app(\App\Services\DocumentoService::class);

            if ($this->modo_edicion && $this->pedido_id) {
                // ====== EDICIÓN ======
                $pedido = Pedido::with('variantes.insumos','variantes.respuestasCampos')
                    ->findOrFail($this->pedido_id);

                // Captura estado ANTES y sucursal anterior
                $inv          = app(InventoryConsumptionService::class);
                $consumoAntes = $inv->consumoAgrupado($pedido->id);
                $oldSucursal  = (int) $pedido->sucursal_elaboracion_id;

                // Actualiza pedido
                $pedido->update($data);

                // Limpia y reconstruye
                foreach ($pedido->variantes as $v) {
                    $v->insumos()->delete();
                    $v->respuestasCampos()->delete();
                    $v->delete();
                }

                foreach ($this->servicios_pedido as $i => $servicio) {
                    // Atributos del PSV si es personalizado
                    $atributosPsv = null;
                    if (($servicio['tipo'] ?? '') === 'personalizado') {
                        $atributosPsv = [
                            'campos_def'            => $servicio['campos_def']            ?? [],
                            'campos_personalizados' => $servicio['campos_personalizados'] ?? [],
                        ];
                    }

                    $psv = PedidoServicioVariante::create([
                        'pedido_id'            => $pedido->id,
                        'servicio_id'          => $servicio['servicio_id'],
                        'nombre_personalizado' => ($servicio['tipo'] ?? '') === 'personalizado'
                            ? ($servicio['nombre'] ?? null) : null,
                        'descripcion'          => $servicio['descripcion'] ?? null,
                        'atributos'            => $atributosPsv,
                        'cantidad'             => $servicio['cantidad'] ?? 1,
                        'precio_unitario'      => $servicio['precio_unitario'],
                        'subtotal'             => $servicio['subtotal'],
                        'total_final'          => $servicio['total_final'] ?? null,
                        'justificacion_total'  => $servicio['justificacion_total'] ?? null,
                        'nota_disenio'         => $servicio['archivo_diseno_nombre'] ?? null,
                        'estado'               => 'en_espera',
                    ]);

                    $this->servicios_pedido[$i]['psv_id'] = $psv->id;

                    if (!empty($servicio['archivo_diseno']) && $servicio['archivo_diseno'] instanceof \Illuminate\Http\UploadedFile) {
                        $docs->subirParaVariante($psv->id, 'archivo_diseno', $servicio['archivo_diseno']);
                        $this->servicios_pedido[$i]['archivo_diseno_nombre'] = $servicio['archivo_diseno']->getClientOriginalName();
                        $psv->update(['nota_disenio' => $this->servicios_pedido[$i]['archivo_diseno_nombre']]);
                    }

                    // === CÁLCULO DE INSUMOS ===
                    if (($servicio['tipo'] ?? 'catalogo') === 'catalogo') {
                        // Catálogo: per-unit desde pivot * cantidad del servicio
                        $srv = \App\Models\Servicio::with('insumos')->find($servicio['servicio_id']);
                        $cantServicio = (int)($servicio['cantidad'] ?? 1);

                        // Mapa de variantes elegidas en UI por INSUMO (solo id y atributos; NO usamos su cantidad)
                        $varMap = [];
                        foreach (($servicio['insumos_usados'] ?? []) as $sel) {
                            $iid = $sel['insumo_id'] ?? $sel['id'] ?? null;
                            if ($iid) {
                                $varMap[$iid] = [
                                    'variante_id' => $sel['variante_id'] ?? null,
                                    'atributos'   => $sel['atributos']   ?? null,
                                ];
                            }
                        }

                        foreach ($srv->insumos as $insumoPivot) {
                            $requerida = (float)$insumoPivot->pivot->cantidad * $cantServicio; // TOTAL a consumir
                            $unidad    = $insumoPivot->pivot->unidad ?? ($insumoPivot->unidad_medida ?? '');

                            // Variante elegida (si la UI la mandó)
                            $choice     = $varMap[$insumoPivot->id] ?? null;
                            $varianteId = $choice['variante_id'] ?? null;

                            // Atributos: UI > DB (variante)
                            $atributos = $choice['atributos'] ?? null;
                            if (!$atributos && $varianteId) {
                                $atributos = \App\Models\VarianteInsumo::find($varianteId)?->atributos;
                            }

                            PedidoInsumo::create([
                                'pedido_servicio_variante_id' => $psv->id,
                                'insumo_id'   => $insumoPivot->id,
                                'unidad'      => $unidad,
                                'cantidad'    => $requerida,   // TOTAL (no se vuelve a multiplicar en consumo)
                                'variante_id' => $varianteId,  // respeta variante
                                'atributos'   => $atributos,   // se guardará como JSON si tienes cast
                            ]);
                        }
                    } else {
                        // Personalizado: tomar TOTAL desde UI (sin multiplicar) y respetar variante/atributos
                        foreach (($servicio['insumos_usados'] ?? []) as $ins) {
                            $insumoId = $ins['insumo_id'] ?? $ins['id'] ?? null;
                            if (!$insumoId) continue;
                            $insumoObj = Insumo::find($insumoId);
                            $unidad    = $ins['unidad'] ?? ($insumoObj->unidad_medida ?? '');
                            PedidoInsumo::create([
                                'pedido_servicio_variante_id' => $psv->id,
                                'insumo_id'   => $insumoId,
                                'unidad'      => $unidad,
                                'cantidad'    => (float)($ins['cantidad'] ?? 1), // TOTAL
                                'variante_id' => $ins['variante_id'] ?? null,
                                'atributos'   => $ins['atributos'] ?? null,
                            ]);
                        }
                    }

                    // Campos personalizados (si hay)
                    foreach (($servicio['campos_personalizados'] ?? []) as $c) {
                        $campoId = $c['id'] ?? null;
                        if (!$campoId) continue;
                        RespuestaCampoPedido::create([
                            'pedido_servicio_variante_id' => $psv->id,
                            'campo_personalizado_id'      => $campoId,
                            'valor'                        => is_array($c['valor'] ?? null)
                                ? json_encode($c['valor'])
                                : ($c['valor'] ?? ''),
                        ]);
                    }
                }

                // Factura (upsert)
                $existente = $pedido->factura;
                if ($this->requiere_factura) {
                    $payload = [
                        'pedido_id'    => $pedido->id,
                        'rfc'          => $this->rfc,
                        'razon_social' => $this->razon_social,
                        'direccion'    => $this->direccion_fiscal,
                        'uso_cfdi'     => $this->uso_cfdi,
                        'metodo_pago'  => $this->metodo_pago_factura,
                    ];
                    $existente ? $existente->update($payload) : FacturaPedido::create($payload);
                } else {
                    if ($existente) $existente->delete();
                }

            } else {
                // ====== CREACIÓN ======
                $pedido = Pedido::create($data);
                $this->pedido_id = $pedido->id;
                $this->refrescarComprobantes();

                foreach ($this->servicios_pedido as $i => $servicio) {
                    $atributosPsv = null;
                    if (($servicio['tipo'] ?? '') === 'personalizado') {
                        $atributosPsv = [
                            'campos_def'            => $servicio['campos_def']            ?? [],
                            'campos_personalizados' => $servicio['campos_personalizados'] ?? [],
                        ];
                    }

                    $psv = PedidoServicioVariante::create([
                        'pedido_id'            => $pedido->id,
                        'servicio_id'          => $servicio['servicio_id'],
                        'nombre_personalizado' => ($servicio['tipo'] ?? '') === 'personalizado'
                            ? ($servicio['nombre'] ?? null) : null,
                        'descripcion'          => $servicio['descripcion'] ?? null,
                        'atributos'            => $atributosPsv,
                        'cantidad'             => $servicio['cantidad'] ?? 1,
                        'precio_unitario'      => $servicio['precio_unitario'],
                        'subtotal'             => $servicio['subtotal'],
                        'total_final'          => $servicio['total_final'] ?? null,
                        'justificacion_total'  => $servicio['justificacion_total'] ?? null,
                        'nota_disenio'         => $servicio['archivo_diseno_nombre'] ?? null,
                        'estado'               => 'en_espera',
                    ]);

                    $this->servicios_pedido[$i]['psv_id'] = $psv->id;

                    if (!empty($servicio['archivo_diseno']) && $servicio['archivo_diseno'] instanceof \Illuminate\Http\UploadedFile) {
                        $docs->subirParaVariante($psv->id, 'archivo_diseno', $servicio['archivo_diseno']);
                        $this->servicios_pedido[$i]['archivo_diseno_nombre'] = $servicio['archivo_diseno']->getClientOriginalName();
                        $psv->update(['nota_disenio' => $this->servicios_pedido[$i]['archivo_diseno_nombre']]);
                    }

                    // === CÁLCULO DE INSUMOS ===
                    if (($servicio['tipo'] ?? 'catalogo') === 'catalogo') {
                        $srv = \App\Models\Servicio::with('insumos')->find($servicio['servicio_id']);
                        $cantServicio = (int)($servicio['cantidad'] ?? 1);

                        // Mapa de variantes elegidas en UI
                        $varMap = [];
                        foreach (($servicio['insumos_usados'] ?? []) as $sel) {
                            $iid = $sel['insumo_id'] ?? $sel['id'] ?? null;
                            if ($iid) {
                                $varMap[$iid] = [
                                    'variante_id' => $sel['variante_id'] ?? null,
                                    'atributos'   => $sel['atributos']   ?? null,
                                ];
                            }
                        }

                        foreach ($srv->insumos as $insumoPivot) {
                            $requerida = (float)$insumoPivot->pivot->cantidad * $cantServicio;
                            $unidad    = $insumoPivot->pivot->unidad ?? ($insumoPivot->unidad_medida ?? '');

                            $choice     = $varMap[$insumoPivot->id] ?? null;
                            $varianteId = $choice['variante_id'] ?? null;
                            $atributos  = $choice['atributos'] ?? null;
                            if (!$atributos && $varianteId) {
                                $atributos = \App\Models\VarianteInsumo::find($varianteId)?->atributos;
                            }

                            PedidoInsumo::create([
                                'pedido_servicio_variante_id' => $psv->id,
                                'insumo_id'   => $insumoPivot->id,
                                'unidad'      => $unidad,
                                'cantidad'    => $requerida, // TOTAL
                                'variante_id' => $varianteId,
                                'atributos'   => $atributos,
                            ]);
                        }
                    } else {
                        foreach (($servicio['insumos_usados'] ?? []) as $ins) {
                            $insumoId = $ins['insumo_id'] ?? $ins['id'] ?? null;
                            if (!$insumoId) continue;
                            $insumoObj = Insumo::find($insumoId);
                            $unidad    = $ins['unidad'] ?? ($insumoObj->unidad_medida ?? '');
                            PedidoInsumo::create([
                                'pedido_servicio_variante_id' => $psv->id,
                                'insumo_id'   => $insumoId,
                                'unidad'      => $unidad,
                                'cantidad'    => (float)($ins['cantidad'] ?? 1), // TOTAL
                                'variante_id' => $ins['variante_id'] ?? null,
                                'atributos'   => $ins['atributos'] ?? null,
                            ]);
                        }
                    }

                    // Campos personalizados
                    foreach (($servicio['campos_personalizados'] ?? []) as $c) {
                        $campoId = $c['id'] ?? null;
                        if (!$campoId) continue;
                        RespuestaCampoPedido::create([
                            'pedido_servicio_variante_id' => $psv->id,
                            'campo_personalizado_id'      => $campoId,
                            'valor'                        => is_array($c['valor'] ?? null)
                                ? json_encode($c['valor'])
                                : ($c['valor'] ?? ''),
                        ]);
                    }
                }

                if ($this->requiere_factura) {
                    FacturaPedido::create([
                        'pedido_id'    => $pedido->id,
                        'rfc'          => $this->rfc,
                        'razon_social' => $this->razon_social,
                        'direccion'    => $this->direccion_fiscal,
                        'uso_cfdi'     => $this->uso_cfdi,
                        'metodo_pago'  => $this->metodo_pago_factura,
                    ]);
                }
            }
        }); // fin transaction

        // 4) Inventario: creación vs edición
        if ($this->modo_edicion && $this->pedido_id) {
            $pedidoRefrescado = Pedido::find($this->pedido_id);
            app(InventoryConsumptionService::class)->ajustarPorEdicion($pedidoRefrescado, $consumoAntes, (int) $oldSucursal);
        } else {
            if ($this->pedido_id) {
                $pedido = Pedido::find($this->pedido_id);
                if ($pedido && $pedido->sucursal_elaboracion_id) {
                    app(InventoryConsumptionService::class)->consumirDesdePedido($pedido);
                }
            }
        }

        session()->flash('mensaje', $this->modo_edicion ? 'Pedido actualizado correctamente' : 'Pedido creado correctamente');
        return redirect()->route('pedidos');
    }




    public function cargarServicioSeleccionado()
    {
        if (!$this->servicio_seleccionado_id) return;

        $servicio = Servicio::with(['camposPersonalizados', 'insumos.variantes'])->find($this->servicio_seleccionado_id);

        if (!$servicio) return;

        $this->campos_personalizados = $servicio->camposPersonalizados->map(function ($campo) {
            return [
                'id' => $campo->id,
                'nombre' => $campo->nombre,
                'tipo' => $campo->tipo,
                'valor' => null, // Aseguramos valor como nulo inicial
                'opciones' => $campo->opciones ?? [],
            ];
        })->toArray();

        $this->insumos_con_variantes = $servicio->insumos->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'variantes' => $insumo->variantes->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'atributos' => is_string($v->atributos) ? json_decode($v->atributos, true) : $v->atributos,
                    ];
                })->values()->toArray(), // 👈
                'variantes_seleccionadas' => [],
            ];
        })->values()->toArray(); // 👈

    }


    public function agregarServicio()
    {
        /* =========================
         * 1) Validaciones base
         * ========================= */
        if ($this->servicio_personalizado) {
            $this->validate([
                'servicio_personalizado_nombre'  => 'required|string|max:255',
                'servicio_personalizado_precio'  => 'required|numeric|min:0',
            ]);
        } else {
            $this->validate([
                'servicio_seleccionado_id' => 'required|exists:servicios,id',
            ]);
        }

        /* ============================================
         * 2) Validación de valores de campos (común)
         * ============================================ */
        foreach ($this->campos_personalizados as $i => $campo) {
            $tipo = $campo['tipo'] ?? 'texto';
            $base = "campos_personalizados.$i.valor";
            switch ($tipo) {
                case 'texto':
                case 'select':
                    $this->validate([$base => 'nullable|string|max:255']);
                    break;
                case 'numero':
                    $this->validate([$base => 'nullable|numeric|min:0']);
                    break;
                case 'booleano':
                    $this->validate([$base => 'nullable|boolean']);
                    break;
            }
        }

        /* ======================================================
         * 3) Preparar insumos usados (ramas: personalizado / catálogo)
         * ====================================================== */
        $insumos_sin_variantes = []; // para añadirlos en cada línea catalogada
        $lineas_a_insertar     = []; // aquí acumulamos las líneas (1 por variante)

        if ($this->servicio_personalizado) {
            // ---------- PERSONALIZADO (SIN VARIANTES) ----------
            $insumos_usados = collect($this->insumos_agregados)->map(function ($insumo) {
                return [
                    'id'        => $insumo['id'],
                    'nombre'    => $insumo['nombre'],
                    'categoria' => $insumo['categoria'],
                    'cantidad'  => $insumo['cantidad'],
                    'unidad'    => $insumo['unidad'],
                ];
            })->toArray();

            // Definición de campos (constructor)
            $defCampos = $this->usar_campos_personalizados
                ? collect($this->campos_personalizados_temporales)->map(fn($c) => [
                    'nombre'   => $c['nombre'],
                    'tipo'     => $c['tipo'],
                    'opciones' => ($c['tipo'] === 'select') ? ($c['opciones'] ?? []) : [],
                ])->toArray()
                : [];

            // Valores iniciales (todos null)
            $valCampos = collect($defCampos)->map(fn($d) => [
                'nombre' => $d['nombre'],
                'tipo'   => $d['tipo'],
                'valor'  => null,
            ])->toArray();

            $precio = (float) $this->servicio_personalizado_precio;

            $nuevo = [
                'tipo'                  => 'personalizado',
                'servicio_id'           => null,
                'nombre'                => $this->servicio_personalizado_nombre,
                'descripcion'           => $this->servicio_personalizado_descripcion,
                'campos_def'            => $defCampos,
                'campos_personalizados' => $valCampos,
                'insumos_usados'        => $insumos_usados,
                'cantidad'              => 1,
                'precio_unitario'       => $precio,
                'subtotal'              => $precio,
                'total_final'           => null,
                'justificacion_total'   => null,
                'archivo_diseno'        => null,
                'archivo_diseno_nombre' => null,

                // Campos para edición por variante (compatibilidad)
                'variante_id'           => null,
                'variante_label'        => null,
            ];

            $lineas_a_insertar[] = $nuevo;

        } else {
            // ---------- CATÁLOGO (1 LÍNEA POR VARIANTE) ----------
            $servicio = Servicio::with('insumos.variantes')->find($this->servicio_seleccionado_id);

            if (!$servicio || !$servicio->activo) {
                $this->addError('servicio_seleccionado_id', 'El servicio seleccionado no existe o fue desactivado.');
                return;
            }

            // Insumos SIN variantes del servicio base (se agregan a cada línea)
            foreach ($servicio->insumos as $ins) {
                if ($ins->variantes->isEmpty()) {
                    $insumos_sin_variantes[] = [
                        'insumo_id'   => $ins->id,
                        'nombre'      => $ins->nombre,
                        'variante_id' => null,
                        'atributos'   => null,
                    ];
                }
            }

            // Precio según tipo de cliente
            $precioBase = $this->tipo_cliente === 'Maquilador'
                ? $servicio->precio_maquilador
                : $servicio->precio_normal;

            $precioUnit = (float) ($precioBase ?? 0);

            // Variantes marcadas en la UI (aplanadas)
            $variantesMarcadas = [];
            foreach ($this->insumos_con_variantes as $insumo) {
                if (!empty($insumo['variantes_seleccionadas']) && is_array($insumo['variantes_seleccionadas'])) {
                    foreach ($insumo['variantes_seleccionadas'] as $variante_id) {
                        $variantesMarcadas[] = (int) $variante_id;
                    }
                }
            }
            $variantesMarcadas = array_values(array_unique($variantesMarcadas));

            if (empty($variantesMarcadas)) {
                // Sin variantes seleccionadas → una sola línea sin variante
                $lineas_a_insertar[] = $this->buildLineaCatalogo(
                    servicio: $servicio,
                    precioUnit: $precioUnit,
                    camposValores: $this->campos_personalizados,
                    insumos_sin_variantes: $insumos_sin_variantes,
                    variante: null
                );
            } else {
                // Por cada variante → UNA línea
                foreach ($variantesMarcadas as $varId) {
                    $variante = VarianteInsumo::with('insumo')->find($varId);

                    if (!$variante) {
                        continue;
                    }

                    $lineas_a_insertar[] = $this->buildLineaCatalogo(
                        servicio: $servicio,
                        precioUnit: $precioUnit,
                        camposValores: $this->campos_personalizados,
                        insumos_sin_variantes: $insumos_sin_variantes,
                        variante: $variante
                    );
                }
            }
        }

        /* ==========================================================
         * 4) Insertar o reemplazar (si se estaba editando una línea)
         * ========================================================== */
        if ($this->indice_edicion_servicio !== null) {
            // Reemplaza la línea en edición por la PRIMERA y el resto las inserta después
            $idx = $this->indice_edicion_servicio;
            $this->servicios_pedido[$idx] = $lineas_a_insertar[0];
            if (count($lineas_a_insertar) > 1) {
                array_splice($this->servicios_pedido, $idx + 1, 0, array_slice($lineas_a_insertar, 1));
            }
        } else {
            // Inserta todas al final
            foreach ($lineas_a_insertar as $linea) {
                $this->servicios_pedido[] = $linea;
            }
        }

        /* =========================
         * 5) Limpiar UI de la sección
         * ========================= */
        if ($this->servicio_personalizado) {
            $this->resetPersonalizadoUi(); // NO apaga el toggle
        }

        $this->resetServicio();
        $this->busqueda_servicio = '';
        $this->mostrar_sugerencias_servicios = false;

        $this->recalcularTotal();
        $this->totals_refresh++;
    }

    /* =========================
     * Helpers privados
     * ========================= */

    private function buildLineaCatalogo($servicio, float $precioUnit, array $camposValores, array $insumos_sin_variantes, $variante = null): array
    {
        // Insumos usados para esta LÍNEA:
        $insumos_usados = $insumos_sin_variantes;

        // Si hay variante, añadimos el insumo+variante concreto
        if ($variante) {
            $insumos_usados[] = [
                'insumo_id'   => $variante->insumo->id ?? null,
                'nombre'      => $variante->insumo->nombre ?? '—',
                'variante_id' => $variante->id,
                'atributos'   => $this->atributosAsociativos($variante->atributos), // 👈 aquí
            ];
        }


        return [
            'tipo'                  => 'catalogo',
            'servicio_id'           => $servicio->id,
            'nombre'                => $servicio->nombre,

            // Copiamos los valores de campos (por variante; luego podrás editarlos por línea)
            'campos_personalizados' => $camposValores,

            'insumos_usados'        => $insumos_usados,

            // Económicos por línea (cantidad por defecto 1)
            'cantidad'              => 1,
            'precio_unitario'       => $precioUnit,
            'subtotal'              => $precioUnit,
            'total_final'           => null,
            'justificacion_total'   => null,

            // Archivo de diseño por línea (se llenará en el modal de edición de ESTA variante)
            'archivo_diseno'        => null,
            'archivo_diseno_nombre' => null,

            // Identificadores de variante (para mostrar/editar por separado)
            'psv_id'                => null,
            'variante_id'           => $variante?->id,
            'variante_label'        => $variante ? $this->formatearLabelVariante($variante) : null,
        ];
    }

    private function formatearLabelVariante($variante): string
    {
        $insumo = $variante->insumo?->nombre ?? '—';

        $a = $variante->atributos;
        if (is_string($a)) { $a = json_decode($a, true); }

        $pares = [];

        // Soportamos:
        // a) asociativo: ['Color'=>'Rojo','Tamaño'=>'M']
        // b) lista de pares: [['atributo'=>'Color','valor'=>'Rojo'], ...]
        if (is_array($a)) {
            $esAsociativo = array_keys($a) !== range(0, count($a) - 1);

            if ($esAsociativo) {
                foreach ($a as $k => $v) {
                    $pares[] = "{$k}: {$v}";
                }
            } else {
                foreach ($a as $item) {
                    if (is_array($item) && isset($item['atributo'], $item['valor'])) {
                        $pares[] = "{$item['atributo']}: {$item['valor']}";
                    }
                }
            }
        }

        $attrs = implode(', ', $pares);
        return $attrs ? "{$insumo} ({$attrs})" : $insumo;
    }







    public function eliminarServicio($index)
    {
        unset($this->servicios_pedido[$index]);
        $this->servicios_pedido = array_values($this->servicios_pedido); // reindexar
        $this->recalcularTotal();
        $this->totals_refresh++;
    }



    public function editarServicio($index)
    {
        $servicio = $this->servicios_pedido[$index] ?? null;
        if (!$servicio) return;

        $this->indice_edicion_servicio = $index;

        // 1) Campos personalizados (normalizados para el modal)
        $this->campos_personalizados = collect($servicio['campos_personalizados'] ?? [])
            ->map(function ($c) {
                $c['valor'] = is_array($c['valor'] ?? null) ? json_encode($c['valor']) : $c['valor'];
                return $c;
            })->toArray();

        // 2) Archivo de diseño (reset temporal + nombre previo)
        $this->archivo_diseno = null;
        $this->archivo_diseno_nombre   = $servicio['archivo_diseno_nombre'] ?? null;
        $this->modal_total_final       = $servicio['total_final'] ?? null;
        $this->modal_justificacion_total = $servicio['justificacion_total'] ?? null;

        // 3) Preparar insumos/variantes según el tipo (SIN tocar el toggle principal)
        $tipo = $servicio['tipo'] ?? (is_null($servicio['servicio_id']) ? 'personalizado' : 'catalogo');

        if ($tipo === 'catalogo') {
            // Servicio base de esta fila (para listar opciones de variante)
            $this->servicio_seleccionado_id = $servicio['servicio_id'] ?? null;
            $this->insumos_con_variantes = []; // aquí solo listamos opciones, NO múltiples seleccionadas

            // Valor actual de la variante de esta fila
            $this->modal_variante_id    = $servicio['variante_id']   ?? null;
            $this->modal_variante_label = $servicio['variante_label'] ?? null;

            if ($this->servicio_seleccionado_id) {
                $servicio_base = Servicio::with('insumos.variantes')->find($this->servicio_seleccionado_id);

                if ($servicio_base) {
                    foreach ($servicio_base->insumos as $insumo) {
                        // Listado simple de variantes disponibles para este insumo
                        $this->insumos_con_variantes[] = [
                            'id'       => $insumo->id,
                            'nombre'   => $insumo->nombre,
                            'variantes'=> $insumo->variantes->map(function ($v) {
                                return [
                                    'id'        => $v->id,
                                    'atributos' => is_string($v->atributos) ? json_decode($v->atributos, true) : $v->atributos,
                                ];
                            })->values()->toArray(),
                        ];
                    }

                    // Si no teníamos guardada la variante_id (caso legacy), intenta inferirla
                    if (!$this->modal_variante_id) {
                        $primeraVar = collect($servicio['insumos_usados'] ?? [])
                            ->first(fn($x) => !empty($x['variante_id']));
                        if ($primeraVar) {
                            $this->modal_variante_id = (int) $primeraVar['variante_id'];
                        }
                    }
                }
            }

        } else {
            // Personalizado: los insumos se muestran tal cual
            $this->insumos_agregados = $servicio['insumos_usados'] ?? [];
            // No tocar $this->servicio_personalizado aquí.
        }

        // 4) Archivo existente (chip) para esta variante (si la fila ya tiene psv)
        $psvId = $servicio['psv_id'] ?? null;
        $this->docvar_actual = null;

        if ($psvId) {
            $this->docvar_actual = ComprobanteVariante::where('pedido_servicio_variante_id', $psvId)
                ->where('tipo', 'archivo_diseno')
                ->latest('id')
                ->first();
        }

        // 5) Reset de banderas útiles del modal
        $this->aplicar_a_todas = false; // por defecto edición es por variante

        // 6) Abrir modal
        $this->modal_servicio_abierto = true;
    }





    public function resetServicio()
    {
        $this->servicio_seleccionado_id = null;
        $this->campos_personalizados = [];
        $this->insumos_con_variantes = [];
        $this->modal_servicio_abierto = false;
        $this->indice_edicion_servicio = null;
        $this->archivo_diseno = null;
        $this->insumos_agregados = [];

        $this->modal_total_final         = null;
        $this->modal_justificacion_total = null;
        $this->aplicar_a_todas = false;
    }

    public function abrirModalServicio($index)
    {
        if (!isset($this->servicios_pedido[$index])) {
            \Log::warning("Intento de editar servicio no existente en índice: {$index}");
            return;
        }

        $this->indice_edicion_servicio = $index;
        $servicio = $this->servicios_pedido[$index];

        $this->servicio_seleccionado_id = $servicio['servicio_id'];
        $this->campos_personalizados = $servicio['campos_personalizados'];

        // Limpiar archivo temporal
        $this->archivo_diseno = null;

        // 🔧 Restaurar nombre del archivo previamente guardado
        $this->archivo_diseno_nombre = $servicio['archivo_diseno_nombre'] ?? null;

        $this->modal_servicio_abierto = true;
    }



    public function guardarEdicionServicio()
    {
        if ($this->indice_edicion_servicio === null) return;

        // Validación de archivo (PDF/CDR), hasta 100 MB
        $this->validate([
            'archivo_diseno' => 'nullable|file|max:102400|mimetypes:application/pdf,application/vnd.corel-draw,application/octet-stream',
        ]);

        // Referencia por índice
        $servicio = &$this->servicios_pedido[$this->indice_edicion_servicio];

        // Tipo (catálogo / personalizado)
        $tipo = $servicio['tipo'] ?? (is_null($servicio['servicio_id']) ? 'personalizado' : 'catalogo');

        /* -------------------------
         * Cantidad / Subtotal
         * ------------------------- */
        $cantidad = (int) max(1, (int)($servicio['cantidad'] ?? 1));
        $precio   = (float) ($servicio['precio_unitario'] ?? 0);
        $servicio['cantidad']        = $cantidad;
        $servicio['precio_unitario'] = $precio;
        $servicio['subtotal']        = round($cantidad * $precio, 2);

        /* -------------------------
         * Total final (override)
         * ------------------------- */
        $tf = $this->modal_total_final;
        $tf = ($tf === '' || $tf === null) ? null : round((float)$tf, 2);

        if ($tf !== null && $tf !== $servicio['subtotal']) {
            if (trim((string)$this->modal_justificacion_total) === '') {
                $this->addError('modal_justificacion_total', 'Explica por qué el total final difiere del subtotal.');
                return;
            }
            $servicio['total_final']         = $tf;
            $servicio['justificacion_total'] = $this->modal_justificacion_total;
        } else {
            $servicio['total_final']         = null;
            $servicio['justificacion_total'] = null;
        }

        /* -------------------------
         * Campos personalizados
         * ------------------------- */
        $servicio['campos_personalizados'] = $this->campos_personalizados;

        /* -------------------------
         * Archivo del modal
         * ------------------------- */
        if ($this->archivo_diseno) {
            $servicio['archivo_diseno']        = $this->archivo_diseno;
            $servicio['archivo_diseno_nombre'] = $this->archivo_diseno->getClientOriginalName();

            // Subida inmediata solo si ya existe en BD (modo edición + psv_id)
            if ($this->modo_edicion && !empty($servicio['psv_id'])) {
                try {
                    $docs = app(\App\Services\DocumentoService::class);

                    if (!empty($servicio['servicio_id']) && $this->aplicar_a_todas) {
                        // Subir una vez y propagar a todas las variantes de ese servicio en este pedido
                        $meta = $docs->subirYDevolverMeta(
                            $this->archivo_diseno,
                            "servicios/{$this->pedido_id}/{$servicio['servicio_id']}"
                        );

                        $psvIds = \App\Models\PedidoServicioVariante::where('pedido_id', $this->pedido_id)
                            ->where('servicio_id', $servicio['servicio_id'])
                            ->pluck('id')
                            ->all();

                        if (!empty($psvIds)) {
                            $docs->crearRegistrosVarianteDesdeMeta($psvIds, 'archivo_diseno', $meta);

                            \App\Models\PedidoServicioVariante::whereIn('id', $psvIds)
                                ->update(['nota_disenio' => $servicio['archivo_diseno_nombre']]);

                            $this->toastJs('success', 'Diseño aplicado a todas las variantes.');
                        } else {
                            $this->toastJs('warning', 'No hay variantes para este servicio en el pedido.');
                        }
                    } else {
                        // Solo esta variante
                        $docs->subirParaVariante((int)$servicio['psv_id'], 'archivo_diseno', $this->archivo_diseno);

                        \App\Models\PedidoServicioVariante::whereKey($servicio['psv_id'])
                            ->update(['nota_disenio' => $servicio['archivo_diseno_nombre']]);

                        $this->toastJs('success', 'Diseño subido a la variante.');
                    }

                    // Limpieza del input y del checkbox
                    $this->archivo_diseno  = null;
                    $this->aplicar_a_todas = false;

                } catch (\Throwable $e) {
                    $this->toastJs('error', 'No se pudo subir el archivo. Intenta nuevamente.');
                    return;
                }
            }
        }

        /* -------------------------
         * Insumos por tipo
         * ------------------------- */
        if ($tipo === 'personalizado') {
            // Copia directa desde el modal
            $servicio['insumos_usados'] = collect($this->insumos_agregados)->map(function ($insumo) {
                return [
                    'id'        => $insumo['id'],
                    'nombre'    => $insumo['nombre'],
                    'categoria' => $insumo['categoria'],
                    'cantidad'  => $insumo['cantidad'],
                    'unidad'    => $insumo['unidad'],
                ];
            })->toArray();

        } else {
            // CATÁLOGO → 1 sola variante por fila: usamos $this->modal_variante_id
            // 1) Mantén insumos SIN variante de la fila
            $base = collect($servicio['insumos_usados'] ?? [])
                ->filter(fn($x) => empty($x['variante_id']))
                ->values()
                ->toArray();

            // 2) Si el modal eligió variante, reemplaza la de la fila
            $varianteId = $this->modal_variante_id ?? null;
            if ($varianteId) {
                $v = \App\Models\VarianteInsumo::with('insumo')

                    ->find($varianteId);

                if ($v) {
                    $base[] = [
                        'insumo_id'   => $v->insumo->id ?? null,
                        'nombre'      => $v->insumo->nombre ?? '—',
                        'variante_id' => $v->id,
                        'atributos'   => $this->atributosAsociativos($v->atributos),
                    ];


                    // Actualiza identidad visible de la línea
                    $servicio['variante_id']    = $v->id;
                    $servicio['variante_label'] = $this->formatearLabelVariante($v);
                }
            } else {
                // Si no eligió variante, la fila puede quedar "sin variante" (si tu servicio lo permite).
                // Si necesitas forzar una, habilita esta validación:
                // $this->addError('modal_variante_id', 'Debes elegir una variante.'); return;
            }

            $servicio['insumos_usados'] = $base;
        }

        // Cierra el modal y refresca totales
        $this->resetServicio();
        $this->recalcularTotal();
        $this->totals_refresh++;
    }







    public function recalcularTotal()
    {
        $this->total = collect($this->servicios_pedido)->sum(function ($s) {
            if (isset($s['total_final']) && $s['total_final'] !== null) {
                return (float) $s['total_final'];
            }
            $cantidad = (int)($s['cantidad'] ?? 1);
            $precio   = (float)($s['precio_unitario'] ?? 0);
            return $cantidad * $precio;
        });

        if ($this->total <= 0) {
            $this->anticipo = 0;
        }
    }



    public function eliminarArchivo()
    {
        $this->archivo_diseno = null;
        $this->archivo_diseno_nombre = null;

        if ($this->indice_edicion_servicio !== null) {
            $this->servicios_pedido[$this->indice_edicion_servicio]['archivo_diseno'] = null;
            $this->servicios_pedido[$this->indice_edicion_servicio]['archivo_diseno_nombre'] = null;
        }

        $this->resetErrorBag('archivo_diseno');
        $this->clearValidation('archivo_diseno');
    }


    public function buscarInsumos()
    {
        if (strlen($this->busqueda_insumo) < 2) {
            $this->insumos_sugeridos = [];
            return;
        }

        $this->insumos_sugeridos = Insumo::with('categoria')
            ->where('nombre', 'ILIKE', '%' . $this->busqueda_insumo . '%')
            ->take(10)
            ->get();
    }





    public function actualizarSugerenciasInsumo()
    {
        if (strlen($this->busqueda_insumo) < 2) {
            $this->insumos_sugeridos = [];
            $this->mostrar_sugerencias_insumo = false;
            return;
        }

        $this->insumos_sugeridos = Insumo::with('categoria')
            ->where('nombre', 'ILIKE', '%' . $this->busqueda_insumo . '%')
            ->limit(5)
            ->get();

        $this->mostrar_sugerencias_insumo = true;
        $this->forzar_render_insumo++;
    }


    public function seleccionarInsumo($id)
    {
        $insumo = Insumo::with('categoria')->find($id);
        if (!$insumo) return;

        $this->insumo_id = $insumo->id;
        $this->busqueda_insumo = $insumo->nombre; // solo como display
        $this->insumo_seleccionado = $insumo;
        $this->mostrar_sugerencias_insumo = false;
    }


    public function agregarInsumo()
    {
        $this->validate([
            'insumo_id'       => 'required|exists:insumos,id',
            'cantidad_insumo' => 'required|numeric|min:0.01',
            'unidad_insumo'   => 'required|string|max:50',
        ]);

        $insumo = Insumo::with('categoria')->find($this->insumo_id);
        if (!$insumo) return;

        foreach ($this->insumos_agregados as &$row) {
            if ($row['id'] === $insumo->id && $row['unidad'] === $this->unidad_insumo) {
                $row['cantidad'] = (float)$row['cantidad'] + (float)$this->cantidad_insumo;
                // reset UI y salir
                $this->insumo_id = null; $this->busqueda_insumo = '';
                $this->cantidad_insumo = 1; $this->unidad_insumo = '';
                $this->mostrar_sugerencias_insumo = false;
                return;
            }
        }
        unset($row);

        $this->insumos_agregados[] = [
            'id'        => $insumo->id,
            'nombre'    => $insumo->nombre,
            'categoria' => $insumo->categoria->nombre ?? 'Sin categoría',
            'cantidad'  => $this->cantidad_insumo,
            'unidad'    => $this->unidad_insumo,
        ];

        $this->insumo_id = null; $this->busqueda_insumo = '';
        $this->cantidad_insumo = 1; $this->unidad_insumo = '';
        $this->mostrar_sugerencias_insumo = false;
    }



    public function quitarInsumo($id)
    {
        $this->insumos_agregados = array_values(array_filter(
            $this->insumos_agregados,
            fn($insumo) => $insumo['id'] !== $id
        ));
    }


    public function getUnidadesExistentesProperty()
    {
        return collect(\App\Models\Insumo::pluck('unidad_medida')->filter())
            ->unique()
            ->values()
            ->toArray();
    }

    public function agregarCampoPersonalizado()
    {
        $this->validate([
            'nuevoCampo.nombre' => 'required|string|max:100',
            'nuevoCampo.tipo' => 'required|in:texto,numero,booleano,select',
            'nuevoCampo.opciones' => 'nullable|string',
        ]);

        $campo = [
            'nombre' => $this->nuevoCampo['nombre'],
            'tipo' => $this->nuevoCampo['tipo'],
            'opciones' => [],
        ];

        if ($campo['tipo'] === 'select') {
            $opciones = array_filter(array_map('trim', explode(',', $this->nuevoCampo['opciones'])));
            if (empty($opciones)) {
                $this->addError('nuevoCampo.opciones', 'Debes ingresar al menos una opción para el campo tipo select.');
                return;
            }
            $campo['opciones'] = $opciones;
        }

        $this->campos_personalizados_temporales[] = $campo;

        $this->nuevoCampo = [ 'nombre' => '', 'tipo' => '', 'opciones' => '' ];
    }


    public function eliminarCampoPersonalizado($index)
    {
        unset($this->campos_personalizados_temporales[$index]);
        $this->campos_personalizados_temporales = array_values($this->campos_personalizados_temporales);
    }

    public function updatedServicioPersonalizado($value)
    {
        if (!$value) {
            $this->usar_campos_personalizados = false;
            $this->campos_personalizados_temporales = [];
            $this->nuevoCampo = ['nombre' => '', 'tipo' => '', 'opciones' => ''];
        }
    }

    public function editarEstructuraPersonalizado($i)
    {
        $s = $this->servicios_pedido[$i];

        // Cerrar modal rápido si estaba abierto
        $this->modal_servicio_abierto = false;

        // Montar formulario de personalizado con datos existentes
        $this->servicio_personalizado = true;
        $this->modo_editar_personalizado = true;
        $this->indice_edicion_personalizado = $i;
        $this->nombre_original_personalizado = $s['nombre'];

        $this->servicio_personalizado_nombre = $s['nombre'] ?? '';
        $this->servicio_personalizado_precio = $s['precio_unitario'] ?? 0;
        $this->servicio_personalizado_descripcion = $s['descripcion'] ?? '';

        $this->insumos_agregados = $s['insumos_usados'] ?? [];

        // Definición y valores de campos
        $this->usar_campos_personalizados = !empty($s['campos_def'] ?? []);
        $this->campos_personalizados_temporales = $s['campos_def'] ?? [];
        $this->campos_personalizados = $s['campos_personalizados'] ?? [];

        // Llevar scroll a la sección
        $this->dispatch('scrollTo', selector: '#servicios-del-pedido');
    }


    public function guardarServicioPersonalizado()
    {
        if ($this->indice_edicion_personalizado === null) return;

        // Reusa la validación de agregar personalizado
        $this->validate([
            'servicio_personalizado_nombre' => 'required|string|max:255',
            'servicio_personalizado_precio' => 'required|numeric|min:0',
        ]);

        // Reconstruye def + valores
        $defCampos = $this->usar_campos_personalizados
            ? collect($this->campos_personalizados_temporales)->map(fn($c) => [
                'nombre' => $c['nombre'],
                'tipo'   => $c['tipo'],
                'opciones' => $c['tipo'] === 'select' ? ($c['opciones'] ?? []) : [],
            ])->toArray()
            : [];

        // Mantén los valores anteriores si puedes mapearlos por nombre
        $valAnt = collect($this->servicios_pedido[$this->indice_edicion_personalizado]['campos_personalizados'] ?? [])
            ->keyBy('nombre');

        $valCampos = collect($defCampos)->map(function ($d) use ($valAnt) {
            return [
                'nombre' => $d['nombre'],
                'tipo'   => $d['tipo'],
                'valor'  => optional($valAnt->get($d['nombre']))['valor'] ?? null,
            ];
        })->toArray();

        $payload = [
            'tipo'                  => 'personalizado',
            'servicio_id'           => null,
            'nombre'                => $this->servicio_personalizado_nombre,
            'descripcion'           => $this->servicio_personalizado_descripcion,
            'campos_def'            => $defCampos,
            'campos_personalizados' => $valCampos,
            'insumos_usados'        => array_values($this->insumos_agregados),
            'cantidad'              => $this->servicios_pedido[$this->indice_edicion_personalizado]['cantidad'] ?? 1,
            'precio_unitario'       => (float) $this->servicio_personalizado_precio,
            'subtotal'              => (float) $this->servicio_personalizado_precio *
                (int)($this->servicios_pedido[$this->indice_edicion_personalizado]['cantidad'] ?? 1),
            'total_final'           => $this->servicios_pedido[$this->indice_edicion_personalizado]['total_final'] ?? null,
            'justificacion_total'   => $this->servicios_pedido[$this->indice_edicion_personalizado]['justificacion_total'] ?? null,
            'archivo_diseno'        => $this->servicios_pedido[$this->indice_edicion_personalizado]['archivo_diseno'] ?? null,
            'archivo_diseno_nombre' => $this->servicios_pedido[$this->indice_edicion_personalizado]['archivo_diseno_nombre'] ?? null,
        ];

        $this->servicios_pedido[$this->indice_edicion_personalizado] = $payload;
        $this->resetPersonalizadoUi();
    }

    public function resetPersonalizadoUi()
    {
        $this->modo_editar_personalizado = false;
        $this->indice_edicion_personalizado = null;
        $this->nombre_original_personalizado = null;

        $this->servicio_personalizado_nombre = '';
        $this->servicio_personalizado_precio = null;
        $this->servicio_personalizado_descripcion = '';
        $this->insumos_agregados = [];
        $this->usar_campos_personalizados = false;
        $this->campos_personalizados_temporales = [];
        $this->campos_personalizados = [];
        // NO tocar $this->servicio_personalizado; el toggle queda como esté
    }



    public function calcularSubtotalServicios(): float
    {
        $suma = 0;
        foreach ($this->servicios_pedido as $srv) {
            $cantidad = (float) ($srv['cantidad'] ?? 1);
            $precio = (float) ($srv['precio_unitario'] ?? 0);
            $suma += ($cantidad * $precio);
        }
        return round($suma, 2);
    }

    // ✅ Restante (propiedad derivada)
    public function getRestanteProperty(): float
    {
        $t = (float) ($this->total ?? 0);
        $a = (float) ($this->anticipo ?? 0);
        $restante = $t - $a;
        // Evitamos -0.00
        return round($restante, 2);
    }

    public function updatedTotal($value)
    {
        $this->total_tocado_manualmente = true;
        $this->total = $this->sanearNumero($value);

        if (round($this->total,2) === round($this->calcularSubtotalServicios(),2)) {
            $this->justificacion_precio = ''; // ✅ opcional
        }

        if ($this->anticipo > $this->total) {
            $this->anticipo = $this->total;
            $this->toastJs('info', 'El anticipo no puede ser mayor que el total. Se ajustó automáticamente.');

        }
    }


    public function updatedAnticipo($value)
    {
        $this->anticipo = $this->sanearNumero($value);
        if ($this->anticipo > $this->total) {
            $this->anticipo = $this->total;
            $this->toastJs('info', 'El anticipo no puede ser mayor que el total. Se ajustó automáticamente.');

        }
    }

    private function sanearNumero($v): float
    {
        $n = (float) ($v === '' ? 0 : $v);
        if ($n < 0) $n = 0;
        return round($n, 2);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Anticipo ≤ Total
            if ($this->anticipo > $this->total) {
                $v->errors()->add('anticipo', 'El anticipo no puede ser mayor que el total.');
            }

        });
    }



    public function updatedServiciosPedido($value, $name)
    {
        // Si cambian cantidad, precio_unitario o total_final de alguna fila, recalcula su subtotal
        if (preg_match('/servicios_pedido\.(\d+)\.(cantidad|precio_unitario|total_final)/', $name, $m)) {
            $i = (int) $m[1];

            $qty    = (int) max(1, (int)($this->servicios_pedido[$i]['cantidad'] ?? 1));
            $precio = (float) ($this->servicios_pedido[$i]['precio_unitario'] ?? 0);

            $this->servicios_pedido[$i]['cantidad']       = $qty; // normaliza
            $this->servicios_pedido[$i]['precio_unitario'] = $precio;
            $this->servicios_pedido[$i]['subtotal']        = round($qty * $precio, 2);
        }

        // Recalcula resumen y sincroniza si el usuario no ha tocado manualmente el total
        $this->recalcularTotal();
        $this->totals_refresh++;
    }

    private function hasLineOverrides(): bool
    {
        return collect($this->servicios_pedido)
            ->contains(fn ($s) => isset($s['total_final']) && $s['total_final'] !== null);
    }


    private function refrescarComprobantes(): void
    {
        if ($this->pedido_id) {
            $this->comprobantes_pedido = ComprobantePedido::where('pedido_id', $this->pedido_id)
                ->latest()
                ->get();
        } else {
            $this->comprobantes_pedido = [];
        }
    }

    public function subirComprobantePedido(): void
    {
        if (!$this->pedido_id) {
            $this->toastJs('warning', 'Primero guarda el pedido para adjuntar archivos.');
            return;
        }

        $this->validate([
            'archivo_comprobante' => 'required|file|max:102400|mimetypes:application/pdf,application/vnd.corel-draw,application/octet-stream',
        ]);

        // Si es REEMPLAZO, borra el anterior antes de subir el nuevo
        if ($this->reemplazar_comprobante_id) {
            $old = ComprobantePedido::find($this->reemplazar_comprobante_id);
            if ($old) {
                try {
                    Storage::disk($old->disk)->delete($old->path);
                } catch (\Throwable $e) {
                    // si falla el borrado físico, igual borra el registro para no duplicar
                }
                $old->delete();
            }
            $this->reemplazar_comprobante_id = null;
        }

        $docs = app(\App\Services\DocumentoService::class);
        $docs->subirParaPedido($this->pedido_id, 'comprobante_pago', $this->archivo_comprobante);

        $this->reset('archivo_comprobante');
        $this->refrescarComprobantes();
        $this->toastJs('success', 'Comprobante subido correctamente.');
    }



    public function subirDisenoVariante(int $psvId): void
    {
        $this->validate([
            'archivo_diseno' => 'required|file|max:102400|mimetypes:application/pdf,application/vnd.corel-draw,application/octet-stream',
        ]);

        $nombre = $this->archivo_diseno->getClientOriginalName();

        $docs = app(DocumentoService::class);
        $docs->subirParaVariante($psvId, 'archivo_diseno', $this->archivo_diseno);

        // nombre visible en BD
        PedidoServicioVariante::whereKey($psvId)->update(['nota_disenio' => $nombre]);

        // refrescar chip actual
        $this->docvar_actual = ComprobanteVariante::where('pedido_servicio_variante_id', $psvId)
            ->where('tipo', 'archivo_diseno')
            ->latest('id')
            ->first();

        // refrescar fila en memoria
        foreach ($this->servicios_pedido as &$s) {
            if (!empty($s['psv_id']) && (int)$s['psv_id'] === (int)$psvId) {
                $s['archivo_diseno_nombre'] = $nombre;
                break;
            }
        }
        unset($s);

        $this->reset('archivo_diseno');
        $this->toastJs('success', 'Diseño subido correctamente a la variante.');
    }



    public function subirDisenoParaServicio(int $servicioId): void
    {
        if (!$this->pedido_id) {
            $this->dispatch('toast', ['type' => 'warning', 'msg' => 'Primero guarda el pedido para adjuntar archivos.']);
            return;
        }

        $this->validate([
            'archivo_diseno_masivo' => 'required|file|max:102400|mimetypes:application/pdf,application/vnd.corel-draw,application/octet-stream',
        ]);

        $docs = app(\App\Services\DocumentoService::class);
        $meta = $docs->subirYDevolverMeta($this->archivo_diseno_masivo, "servicios/{$this->pedido_id}/{$servicioId}");

        $psvIds = \App\Models\PedidoServicioVariante::where('pedido_id', $this->pedido_id)
            ->where('servicio_id', $servicioId)
            ->pluck('id')
            ->all();

        if (empty($psvIds)) {
            $this->toastJs('warning', 'No hay variantes para este servicio en el pedido.');

            return;
        }

        $docs->crearRegistrosVarianteDesdeMeta($psvIds, 'archivo_diseno', $meta);

        $this->reset('archivo_diseno_masivo');
        $this->toastJs('success', 'Diseño aplicado a todas las variantes del servicio.');


    }


// Descarga un comprobante del pedido por ID del registro
    public function descargarComprobantePedido(int $comprobanteId): void
    {
        $comp = ComprobantePedido::find($comprobanteId);
        if (!$comp) {
            $this->toastJs('error', 'Archivo no encontrado.');
            return;
        }

        $url = app(\App\Services\DocumentoService::class)->urlDescarga(
            $comp->disk,
            $comp->path,
            $comp->original_name,
            $comp->mime,
            10 // minutos
        );

        $this->dispatch('abrir-url', url: $url);
    }

// Descarga el último “archivo de diseño” de una variante (para el modal de edición)
    public function descargarDisenoDeVariante(int $psvId): void
    {
        $comp = \App\Models\ComprobanteVariante::where('pedido_servicio_variante_id', $psvId)
            ->where('tipo', 'archivo_diseno')
            ->latest('id')
            ->first();

        if (!$comp) {
            $this->toastJs('warning', 'Esta variante no tiene archivo de diseño.');

            return;
        }

        $url = app(\App\Services\DocumentoService::class)->urlDescarga(
            $comp->disk,
            $comp->path,
            $comp->original_name,
            $comp->mime,
            10
        );

        $this->dispatch('abrir-url', url: $url);
    }

    private function toastJs(string $tipo, string $mensaje): void
    {
        $mensaje = addslashes($mensaje); // por si lleva comillas
        $this->js(<<<JS
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { tipo: '{$tipo}', mensaje: '{$mensaje}' }
        }));
    JS);
    }


    public function prepararReemplazoComprobante(int $comprobanteId): void
    {
        $this->reemplazar_comprobante_id = $comprobanteId;

        // abre el input de archivo del dropzone (ajusta el id si usas otro)
        $this->dispatch('abrir-input-comprobante');
        $this->toastJs('info', 'Selecciona el archivo para reemplazar el comprobante.');
    }


    public function eliminarComprobantePedido(int $comprobanteId): void
    {
        // 1) Lo quitamos de la UI al instante
        $this->comprobantes_pedido = collect($this->comprobantes_pedido)
            ->reject(fn ($c) => (int)$c->id === (int)$comprobanteId)
            ->values();

        // 2) Borrado físico + registro
        $comp = ComprobantePedido::find($comprobanteId);
        if (!$comp) {
            $this->toastJs('error', 'Comprobante no encontrado.');
            return;
        }

        try {
            Storage::disk($comp->disk)->delete($comp->path);
        } catch (\Throwable $e) {
            // si falla el borrado de archivo, igual borramos el registro para mantener UI limpia
        }

        $comp->delete();

        // 3) Refrescar lista desde BD por si hay otros cambios
        $this->refrescarComprobantes();
        $this->toastJs('success', 'Comprobante eliminado.');
    }


    public function prepararReemplazoDiseno(int $psvId): void
    {
        $this->reemplazar_diseno_psv_id = $psvId;
        $this->dispatch('abrir-input-diseno'); // el Blade hará click al input oculto
    }


    public function eliminarDisenoDeVariante(int $psvId): void
    {
        $comp = ComprobanteVariante::where('pedido_servicio_variante_id', $psvId)
            ->where('tipo', 'archivo_diseno')
            ->latest('id')
            ->first();

        if (!$comp) {
            $this->toastJs('warning', 'Esta variante no tiene archivo de diseño.');
            return;
        }

        try {
            Storage::disk($comp->disk)->delete($comp->path);
        } catch (\Throwable $e) {
            // si falla el borrado físico, de todos modos seguimos
        }

        $comp->delete();

        // Limpia nombre visible en BD
        PedidoServicioVariante::whereKey($psvId)->update(['nota_disenio' => null]);

        // Actualiza UI inmediata
        if ($this->docvar_actual && (int)($this->docvar_actual->pedido_servicio_variante_id) === (int)$psvId) {
            $this->docvar_actual = null;
        }
        foreach ($this->servicios_pedido as &$s) {
            if (!empty($s['psv_id']) && (int)$s['psv_id'] === (int)$psvId) {
                $s['archivo_diseno_nombre'] = null;
                break;
            }
        }
        unset($s);

        $this->toastJs('success', 'Diseño eliminado de la variante.');
    }


    public function updatedArchivoDiseno()
    {
        $this->resetErrorBag('archivo_diseno');
        $this->clearValidation('archivo_diseno');
    }

    public function eliminarDisenoActual(): void
    {
        // Sin índice o sin psv_id → solo limpia UI
        if ($this->indice_edicion_servicio === null) { $this->eliminarArchivo(); return; }

        $psvId = $this->servicios_pedido[$this->indice_edicion_servicio]['psv_id'] ?? null;
        if (!$psvId) { $this->eliminarArchivo(); return; }

        $comp = \App\Models\ComprobanteVariante::where('pedido_servicio_variante_id', $psvId)
            ->where('tipo', 'archivo_diseno')
            ->latest('id')
            ->first();

        if ($comp) {
            try { \Storage::disk($comp->disk)->delete($comp->path); } catch (\Throwable $e) {}
            $comp->delete();
        }

        // Limpia nombre visible en la variante
        \App\Models\PedidoServicioVariante::whereKey($psvId)->update(['nota_disenio' => null]);

        // Refresca estado en UI
        $this->docvar_actual = null;
        $this->archivo_diseno = null;
        $this->archivo_diseno_nombre = null;
        $this->servicios_pedido[$this->indice_edicion_servicio]['archivo_diseno'] = null;
        $this->servicios_pedido[$this->indice_edicion_servicio]['archivo_diseno_nombre'] = null;

        $this->resetErrorBag('archivo_diseno');
        $this->clearValidation('archivo_diseno');

        $this->toastJs('success', 'Archivo de diseño eliminado.');
    }

    public function dividirEnUnidades(int $index): void
    {
        if (!isset($this->servicios_pedido[$index])) return;

        $linea = $this->servicios_pedido[$index];
        $cantidad = (int) max(1, (int)($linea['cantidad'] ?? 1));

        if ($cantidad <= 1) {
            $this->toastJs('info', 'Esta línea ya es una unidad.');
            return;
        }

        // Calcular prorrateo si hay override
        $tieneOverride = isset($linea['total_final']) && $linea['total_final'] !== null;
        $unitFinal = null;
        if ($tieneOverride) {
            $unitFinal = round(((float)$linea['total_final']) / $cantidad, 2);
        }

        $copias = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $nueva = $this->clonarLineaComoUnidad($linea);

            // Archivo/diseño sólo en la primera
            if ($i === 0) {
                // conserva nombre si ya estaba
                $nueva['archivo_diseno']        = $linea['archivo_diseno']        ?? null;
                $nueva['archivo_diseno_nombre'] = $linea['archivo_diseno_nombre'] ?? null;
            } else {
                $nueva['archivo_diseno']        = null;
                $nueva['archivo_diseno_nombre'] = null;
            }

            // Prorrateo del total_final si existía
            if ($tieneOverride) {
                $nueva['total_final']         = $unitFinal;
                $nueva['justificacion_total'] = trim((string)($linea['justificacion_total'] ?? '')) . ' (prorrateado)';
            }

            $copias[] = $nueva;
        }

        // Reemplaza la línea original por las copias
        array_splice($this->servicios_pedido, $index, 1, $copias);

        $this->recalcularTotal();
        $this->totals_refresh++;
        $this->toastJs('success', 'La línea fue dividida en unidades.');
    }

    /**
     * Clona una línea para convertirla en "unidad" (cantidad = 1).
     * NO toca la variante, ni campos, ni insumos; limpia psv_id y deja subtotal recalculado.
     */
    private function clonarLineaComoUnidad(array $linea): array
    {
        $precio = (float)($linea['precio_unitario'] ?? 0);

        return [
            // identidad y tipo
            'tipo'          => $linea['tipo']          ?? 'catalogo',
            'servicio_id'   => $linea['servicio_id']   ?? null,
            'nombre'        => $linea['nombre']        ?? null,
            'descripcion'   => $linea['descripcion']   ?? null,

            // campos personalizados (valores por línea)
            'campos_personalizados' => $linea['campos_personalizados'] ?? [],
            'campos_def'            => $linea['campos_def']            ?? [],

            // insumos (con o sin variante)
            'insumos_usados' => $linea['insumos_usados'] ?? [],

            // económicos por unidad
            'cantidad'        => 1,
            'precio_unitario' => $precio,
            'subtotal'        => $precio,

            // override (se setea arriba si aplica; aquí por defecto limpio)
            'total_final'         => null,
            'justificacion_total' => null,

            // archivo por unidad (se sobreescribe arriba en la 1ª)
            'archivo_diseno'        => null,
            'archivo_diseno_nombre' => null,

            // vínculo a BD: al dividir aún no existe en BD → psv nulo
            'psv_id'         => null,

            // identidad visible de variante (se conserva)
            'variante_id'    => $linea['variante_id']    ?? null,
            'variante_label' => $linea['variante_label'] ?? null,
        ];
    }

    public function agruparConSiguiente(int $index): void
    {
        if (!isset($this->servicios_pedido[$index], $this->servicios_pedido[$index+1])) return;

        $a = $this->servicios_pedido[$index];
        $b = $this->servicios_pedido[$index+1];

        if (!$this->sonLineasCompatibles($a, $b)) {
            $this->toastJs('warning', 'Las líneas no son compatibles para agrupar.');
            return;
        }

        // Suma de cantidades y recomputo de subtotal
        $cantidad = (int)($a['cantidad'] ?? 1) + (int)($b['cantidad'] ?? 1);
        $precio   = (float)($a['precio_unitario'] ?? 0);

        $a['cantidad']  = $cantidad;
        $a['subtotal']  = round($cantidad * $precio, 2);

        // Limpia cualquier vínculo/archivo en la agrupada (por seguridad)
        $a['psv_id'] = null;
        $a['archivo_diseno'] = null;
        $a['archivo_diseno_nombre'] = null;
        $a['total_final'] = null;
        $a['justificacion_total'] = null;

        // Reemplaza A por A+ B y elimina B
        $this->servicios_pedido[$index] = $a;
        array_splice($this->servicios_pedido, $index+1, 1);

        $this->recalcularTotal();
        $this->totals_refresh++;
        $this->toastJs('success', 'Líneas agrupadas.');
    }

    private function sonLineasCompatibles(array $a, array $b): bool
    {
        // No unir si alguna tiene override o archivo (porque la idea es una sola unidad editable)
        $tieneOverrideOArchivo = function ($x) {
            return !empty($x['total_final']) || !empty($x['archivo_diseno']) || !empty($x['archivo_diseno_nombre']);
        };
        if ($tieneOverrideOArchivo($a) || $tieneOverrideOArchivo($b)) return false;

        // Comparaciones clave
        $mismoBasico =
            ($a['tipo']        ?? null) === ($b['tipo']        ?? null) &&
            ($a['servicio_id'] ?? null) === ($b['servicio_id'] ?? null) &&
            ($a['variante_id'] ?? null) === ($b['variante_id'] ?? null) &&
            (float)($a['precio_unitario'] ?? 0) === (float)($b['precio_unitario'] ?? 0);

        if (!$mismoBasico) return false;

        // Campos personalizados iguales (por nombre/tipo/valor)
        if (json_encode($a['campos_personalizados'] ?? []) !== json_encode($b['campos_personalizados'] ?? [])) {
            return false;
        }

        // Insumos usados iguales
        if (json_encode($a['insumos_usados'] ?? []) !== json_encode($b['insumos_usados'] ?? [])) {
            return false;
        }

        return true;
    }

    private function atributosAsociativos($atributos): ?array
    {
        if (is_string($atributos)) {
            $atributos = json_decode($atributos, true);
        }
        if (!is_array($atributos) || empty($atributos)) return null;

        // Si ya es asociativo, lo regresamos tal cual
        $esAsociativo = array_keys($atributos) !== range(0, count($atributos) - 1);
        if ($esAsociativo) return $atributos;

        // Si viene como lista de pares, lo convertimos
        $out = [];
        foreach ($atributos as $item) {
            if (is_array($item) && isset($item['atributo'], $item['valor'])) {
                $out[$item['atributo']] = $item['valor'];
            }
        }
        return $out ?: null;
    }

}


