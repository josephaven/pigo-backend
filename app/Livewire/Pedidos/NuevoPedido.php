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
        // Trae lo necesario en un solo viaje
        $pedido = Pedido::with([
            'cliente',
            'factura',
            'variantes.servicio',
            'variantes.insumos.insumo.categoria',
            'variantes.respuestasCampos.campo',
        ])->findOrFail($id);

        // ---------- generales ----------
        $this->pedido_id               = $pedido->id;
        $this->cliente_id              = $pedido->cliente_id;
        $this->cliente_seleccionado    = $pedido->cliente;
        $this->busqueda_cliente        = optional($pedido->cliente)->nombre_completo ?? '';

        $this->sucursal_entrega_id     = $pedido->sucursal_entrega_id;
        $this->sucursal_elaboracion_id = $pedido->sucursal_elaboracion_id;
        $this->fecha_entrega = optional($pedido->fecha_entrega)->toDateString(); // 'Y-m-d'
        $this->anticipo                = $pedido->anticipo;
        $this->total                   = $pedido->total;
        $this->justificacion_precio    = $pedido->justificacion_precio;
        $this->metodo_pago_id          = $pedido->metodo_pago_id;

        // ---------- factura (si hay) ----------
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

        // ---------- reconstruir filas ----------
        $this->servicios_pedido = collect($pedido->variantes)->map(function ($row) {
            $esPersonalizado = is_null($row->servicio_id);


            // Lee atributos (si existen) de la variante
            $attrs = null;
            if (!empty($row->atributos)) {
                $tmp = is_string($row->atributos)
                    ? json_decode($row->atributos, true)
                    : $row->atributos;

                if (is_array($tmp)) {
                    $attrs = $tmp;
                }
            }


            // Nombre visible
            $nombre = $esPersonalizado
                ? ($row->nombre_personalizado ?? 'Servicio personalizado')
                : optional($row->servicio)->nombre;


            // Campos personalizados:
            // - Catálogo: desde respuestas
            // - Personalizado: desde atributos guardados
            if ($esPersonalizado) {
                $campos = $attrs['campos_personalizados'] ?? [];
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
                $campos_def = []; // no aplica para catálogo
            }

            // Insumos usados (de pedido_insumo)
            $insumos = collect($row->insumos ?? [])->map(function ($pi) {
                $attrsPi = $pi->atributos ?? null;
                if (is_string($attrsPi)) {
                    $decoded = json_decode($attrsPi, true);
                    if (is_array($decoded)) $attrsPi = $decoded;
                }
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


            return [
                'psv_id'                => $row->id,
                'tipo'                  => $esPersonalizado ? 'personalizado' : 'catalogo',
                'servicio_id'           => $row->servicio_id,   // null si es personalizado
                'nombre'                => $nombre,
                'descripcion'           => $row->descripcion,
                'campos_personalizados' => $campos,
                'campos_def'            => $campos_def,          // 👈 para reconstruir el builder
                'insumos_usados'        => $insumos,
                'cantidad'              => (int) $row->cantidad,
                'precio_unitario'       => (float) $row->precio_unitario,
                'subtotal'              => (float) $row->subtotal,
                'total_final'           => $row->total_final ?? null,           // ← ahora sí
                'justificacion_total'   => $row->justificacion_total ?? null,   // ← ahora sí
                'archivo_diseno'        => null,
                'archivo_diseno_nombre' => $row->nota_disenio,
            ];
        })->toArray();

        // Limpieza de helpers de selección
        $this->servicio_seleccionado_id   = null;
        $this->campos_personalizados      = [];
        $this->insumos_con_variantes      = [];
        $this->busqueda_servicio          = '';
        $this->servicio_personalizado     = false;
        $this->usar_campos_personalizados = false;

        $this->total_tocado_manualmente = true;
        $this->refrescarComprobantes();

    }



    public function guardar()
    {
        // Al menos un servicio
        if (count($this->servicios_pedido) === 0) {
            $this->addError('servicios_pedido', 'Debes agregar al menos un servicio al pedido.');
            return;
        }

        // ÚNICA llamada: toma reglas dinámicas de rules()
        $this->validate();

        // Cliente nuevo (con reglas ya validadas)
        if ($this->cliente_nuevo) {
            $nuevo = Cliente::create([
                'nombre_completo'  => $this->nombre_cliente,
                'telefono'         => $this->telefono_cliente,
                'tipo_cliente'     => $this->tipo_cliente,
                'ocupacion'        => $this->ocupacion_cliente,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sucursal_id'      => $this->sucursal_registro_id,
            ]);
            $this->cliente_id   = $nuevo->id;
            $this->cliente_nuevo = false;
            $this->toastJs('success', 'Cliente nuevo registrado correctamente');
        }

        DB::transaction(function () {
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

            $docs = app(\App\Services\DocumentoService::class); // 👈 lo usamos en ambos flujos

            if ($this->modo_edicion && $this->pedido_id) {
                $pedido = Pedido::with('variantes.insumos','variantes.respuestasCampos')->findOrFail($this->pedido_id);
                $pedido->update($data);

                foreach ($pedido->variantes as $v) {
                    $v->insumos()->delete();
                    $v->respuestasCampos()->delete();
                    $v->delete();
                }

                foreach ($this->servicios_pedido as $servicio) {

                    $atributos = null;
                    if (($servicio['tipo'] ?? '') === 'personalizado') {
                        $atributos = [
                            'campos_def'            => $servicio['campos_def']            ?? [],
                            'campos_personalizados' => $servicio['campos_personalizados'] ?? [],
                        ];
                    }

                    $variante = PedidoServicioVariante::create([
                        'pedido_id'            => $pedido->id,
                        'servicio_id'          => $servicio['servicio_id'],
                        'nombre_personalizado' => ($servicio['tipo'] ?? '') === 'personalizado' ? ($servicio['nombre'] ?? null) : null,
                        'descripcion'          => $servicio['descripcion'] ?? null,
                        'atributos'            => $atributos,
                        'cantidad'             => $servicio['cantidad'] ?? 1,
                        'precio_unitario'      => $servicio['precio_unitario'],
                        'subtotal'             => $servicio['subtotal'],
                        'total_final'          => $servicio['total_final'] ?? null,
                        'justificacion_total'  => $servicio['justificacion_total'] ?? null,
                        'nota_disenio'         => $servicio['archivo_diseno_nombre'] ?? null,
                        'estado'               => 'en_espera',
                    ]);

                    // 👇 SUBE ARCHIVO PENDIENTE DE ESA FILA (si llegó desde el modal)
                    if (!empty($servicio['archivo_diseno']) && $servicio['archivo_diseno'] instanceof \Illuminate\Http\UploadedFile) {
                        $docs->subirParaVariante($variante->id, 'archivo_diseno', $servicio['archivo_diseno']);
                    }

                    foreach (($servicio['insumos_usados'] ?? []) as $ins) {
                        $insumoId = $ins['insumo_id'] ?? $ins['id'] ?? null;
                        if (!$insumoId) continue;
                        $insumoObj = Insumo::find($insumoId);
                        $unidad    = $ins['unidad'] ?? ($insumoObj->unidad_medida ?? '');
                        PedidoInsumo::create([
                            'pedido_servicio_variante_id' => $variante->id,
                            'insumo_id'   => $insumoId,
                            'unidad'      => $unidad,
                            'cantidad'    => $ins['cantidad'] ?? 1,
                            'variante_id' => $ins['variante_id'] ?? null,
                            'atributos'   => !empty($ins['atributos'])
                                ? (is_string($ins['atributos']) ? $ins['atributos'] : json_encode($ins['atributos']))
                                : null,
                        ]);
                    }

                    foreach (($servicio['campos_personalizados'] ?? []) as $c) {
                        $campoId = $c['id'] ?? null;
                        if (!$campoId) continue;
                        RespuestaCampoPedido::create([
                            'pedido_servicio_variante_id' => $variante->id,
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

                return; // fin edición
            }

            // Creación
            $pedido = Pedido::create($data);
            $this->pedido_id = $pedido->id;
            $this->refrescarComprobantes();

            foreach ($this->servicios_pedido as $servicio) {

                $atributos = null;
                if (($servicio['tipo'] ?? '') === 'personalizado') {
                    $atributos = [
                        'campos_def'            => $servicio['campos_def']            ?? [],
                        'campos_personalizados' => $servicio['campos_personalizados'] ?? [],
                    ];
                }

                $variante = PedidoServicioVariante::create([
                    'pedido_id'            => $pedido->id,
                    'servicio_id'          => $servicio['servicio_id'],
                    'nombre_personalizado' => ($servicio['tipo'] ?? '') === 'personalizado' ? ($servicio['nombre'] ?? null) : null,
                    'descripcion'          => $servicio['descripcion'] ?? null,
                    'atributos'            => $atributos,
                    'cantidad'             => $servicio['cantidad'] ?? 1,
                    'precio_unitario'      => $servicio['precio_unitario'],
                    'subtotal'             => $servicio['subtotal'],
                    'total_final'          => $servicio['total_final'] ?? null,
                    'justificacion_total'  => $servicio['justificacion_total'] ?? null,
                    'nota_disenio'         => $servicio['archivo_diseno_nombre'] ?? null,
                    'estado'               => 'en_espera',
                ]);

                // 👇 SUBE ARCHIVO PENDIENTE DE ESA FILA (si llegó desde el modal)
                if (!empty($servicio['archivo_diseno']) && $servicio['archivo_diseno'] instanceof \Illuminate\Http\UploadedFile) {
                    $docs->subirParaVariante($variante->id, 'archivo_diseno', $servicio['archivo_diseno']);
                }

                foreach (($servicio['insumos_usados'] ?? []) as $ins) {
                    $insumoId = $ins['insumo_id'] ?? $ins['id'] ?? null;
                    if (!$insumoId) continue;
                    $insumoObj = Insumo::find($insumoId);
                    $unidad    = $ins['unidad'] ?? ($insumoObj->unidad_medida ?? '');
                    PedidoInsumo::create([
                        'pedido_servicio_variante_id' => $variante->id,
                        'insumo_id'   => $insumoId,
                        'unidad'      => $unidad,
                        'cantidad'    => $ins['cantidad'] ?? 1,
                        'variante_id' => $ins['variante_id'] ?? null,
                        'atributos'   => !empty($ins['atributos'])
                            ? (is_string($ins['atributos']) ? $ins['atributos'] : json_encode($ins['atributos']))
                            : null,
                    ]);
                }

                foreach (($servicio['campos_personalizados'] ?? []) as $c) {
                    $campoId = $c['id'] ?? null;
                    if (!$campoId) continue;
                    RespuestaCampoPedido::create([
                        'pedido_servicio_variante_id' => $variante->id,
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
        });

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
        // 1) Validaciones base
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

        // 2) Validación de valores de campos (común)
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

        // 3) Preparar insumos usados
        $insumos_usados = [];
        if ($this->servicio_personalizado) {
            // Insumos simples definidos en el form
            $insumos_usados = collect($this->insumos_agregados)->map(function ($insumo) {
                return [
                    'id'        => $insumo['id'],
                    'nombre'    => $insumo['nombre'],
                    'categoria' => $insumo['categoria'],
                    'cantidad'  => $insumo['cantidad'],
                    'unidad'    => $insumo['unidad'],
                ];
            })->toArray();
        } else {
            // Insumos con variantes
            foreach ($this->insumos_con_variantes as $insumo) {
                if (!empty($insumo['variantes_seleccionadas']) && is_array($insumo['variantes_seleccionadas'])) {
                    foreach ($insumo['variantes_seleccionadas'] as $variante_id) {
                        $variante = VarianteInsumo::find($variante_id);
                        if ($variante) {
                            $insumos_usados[] = [
                                'insumo_id' => $insumo['id'],
                                'nombre'    => $insumo['nombre'],
                                'variante_id' => $variante->id,
                                'atributos' => is_string($variante->atributos)
                                    ? json_decode($variante->atributos, true)
                                    : $variante->atributos,
                            ];
                        }
                    }
                }
            }
        }

        // 4) Crear payload del servicio
        if ($this->servicio_personalizado) {
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

            $nuevo_servicio = [
                'tipo'                  => 'personalizado',
                'servicio_id'           => null,
                'nombre'                => $this->servicio_personalizado_nombre,
                'descripcion'           => $this->servicio_personalizado_descripcion,
                'campos_def'            => $defCampos,      // <-- definición
                'campos_personalizados' => $valCampos,      // <-- valores
                'insumos_usados'        => $insumos_usados,
                'cantidad'              => 1,
                'precio_unitario'       => $precio,
                'subtotal'              => $precio,
                'total_final'           => null,
                'justificacion_total'   => null,
                'archivo_diseno'        => null,
                'archivo_diseno_nombre' => null,
            ];
        } else {
            // ====== Catálogo (defensivo + insumos sin variantes) ======
            $servicio = Servicio::with('insumos.variantes')->find($this->servicio_seleccionado_id);

            if (!$servicio || !$servicio->activo) {
                $this->addError('servicio_seleccionado_id', 'El servicio seleccionado no existe o fue desactivado.');
                return;
            }

            // Añade los insumos SIN variantes que tenga el servicio base (si aún no están)
            foreach ($servicio->insumos as $ins) {
                if ($ins->variantes->isEmpty()) {
                    $yaIncluido = collect($insumos_usados)->first(fn($u) => ($u['insumo_id'] ?? null) === $ins->id);
                    if (!$yaIncluido) {
                        $insumos_usados[] = [
                            'insumo_id'   => $ins->id,
                            'nombre'      => $ins->nombre,
                            'variante_id' => null,
                            'atributos'   => null,
                        ];
                    }
                }
            }

            $precioBase = $this->tipo_cliente === 'Maquilador'
                ? $servicio->precio_maquilador
                : $servicio->precio_normal;

            // Fallback si viene null
            $precio = (float) ($precioBase ?? 0);

            $nuevo_servicio = [
                'tipo'                  => 'catalogo',
                'servicio_id'           => $servicio->id,
                'nombre'                => $servicio->nombre,
                // Para catálogo ya traes los campos con su 'valor' en $this->campos_personalizados
                'campos_personalizados' => $this->campos_personalizados,
                'insumos_usados'        => $insumos_usados,
                'cantidad'              => 1,
                'precio_unitario'       => $precio,
                'subtotal'              => $precio,
                'total_final'           => null,
                'justificacion_total'   => null,
                'archivo_diseno'        => null,
                'archivo_diseno_nombre' => null,
            ];
        }


        // 5) Insertar o reemplazar si se estaba editando
        if ($this->indice_edicion_servicio !== null) {
            $this->servicios_pedido[$this->indice_edicion_servicio] = $nuevo_servicio;
        } else {
            $this->servicios_pedido[] = $nuevo_servicio;
        }

        // 6) Limpiar UI de la sección
        // 👉 si fue personalizado, limpia también los campos del formulario de personalizado
        if ($this->servicio_personalizado) {
            $this->resetPersonalizadoUi(); // NO apaga el toggle
        }

        // Limpieza común
        $this->resetServicio();
        $this->busqueda_servicio = '';
        $this->mostrar_sugerencias_servicios = false;
        $this->recalcularTotal();
        $this->totals_refresh++;
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

        // 1) Campos personalizados (valores) → normalizados para el modal
        $this->campos_personalizados = collect($servicio['campos_personalizados'] ?? [])
            ->map(function ($c) {
                $c['valor'] = is_array($c['valor'] ?? null) ? json_encode($c['valor']) : $c['valor'];
                return $c;
            })->toArray();

        // 2) Archivo de diseño (reset temporal + nombre previo)
        $this->archivo_diseno = null;
        $this->archivo_diseno_nombre = $servicio['archivo_diseno_nombre'] ?? null;
        $this->modal_total_final = $servicio['total_final'] ?? null;
        $this->modal_justificacion_total = $servicio['justificacion_total'] ?? null;


        // 3) Preparar insumos según el tipo (SIN tocar el toggle)
        $tipo = $servicio['tipo'] ?? (is_null($servicio['servicio_id']) ? 'personalizado' : 'catalogo');

        if ($tipo === 'catalogo') {
            // Reconstruir insumos + variantes para el modal
            $this->servicio_seleccionado_id = $servicio['servicio_id'];
            $this->insumos_con_variantes = [];

            $servicio_base = Servicio::with('insumos.variantes')->find($this->servicio_seleccionado_id);
            if ($servicio_base) {
                foreach ($servicio_base->insumos as $insumo) {
                    $variantes_usadas = collect($servicio['insumos_usados'] ?? [])
                        ->where('insumo_id', $insumo->id)
                        ->pluck('variante_id')
                        ->toArray();

                    $this->insumos_con_variantes[] = [
                        'id' => $insumo->id,
                        'nombre' => $insumo->nombre,
                        'variantes' => $insumo->variantes->map(function ($v) {
                            return [
                                'id' => $v->id,
                                'atributos' => is_string($v->atributos) ? json_decode($v->atributos, true) : $v->atributos,
                            ];
                        }),
                        'variantes_seleccionadas' => $variantes_usadas,
                    ];
                }
            }
        } else {
            // Personalizado: si quieres mostrar los insumos en el modal, los pasas tal cual
            $this->insumos_agregados = $servicio['insumos_usados'] ?? [];
            // Importante: NO tocar $this->servicio_personalizado aquí.
        }
        $psvId = $servicio['psv_id'] ?? null;
        $this->docvar_actual = null;

        if ($psvId) {
            $this->docvar_actual = ComprobanteVariante::where('pedido_servicio_variante_id', $psvId)
                ->where('tipo', 'archivo_diseno')
                ->latest('id')
                ->first();
        }
        // 4) Abrir modal
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

        // Solo PDF/CDR, 100 MB (igual que en tu guardar())
        $this->validate([
            'archivo_diseno' => 'nullable|file|max:102400|mimetypes:application/pdf,application/vnd.corel-draw,application/octet-stream',
        ]);

        // Fila actual
        $servicio = &$this->servicios_pedido[$this->indice_edicion_servicio];

        // Tipo derivado
        $tipo = $servicio['tipo'] ?? (is_null($servicio['servicio_id']) ? 'personalizado' : 'catalogo');

        // Normaliza cantidad y subtotal
        $cantidad = (int) max(1, (int)($servicio['cantidad'] ?? 1));
        $precio   = (float) ($servicio['precio_unitario'] ?? 0);
        $servicio['cantidad']        = $cantidad;
        $servicio['precio_unitario'] = $precio;
        $servicio['subtotal']        = round($cantidad * $precio, 2);

        // Override de total final + justificación
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

        // Guardar valores de campos personalizados del modal
        $servicio['campos_personalizados'] = $this->campos_personalizados;

        // === Archivo del modal ===
        if ($this->archivo_diseno) {
            $servicio['archivo_diseno']        = $this->archivo_diseno;
            $servicio['archivo_diseno_nombre'] = $this->archivo_diseno->getClientOriginalName();

            // Subida inmediata solo si ya existe en BD (modo edición + psv_id)
            if ($this->modo_edicion && !empty($servicio['psv_id'])) {
                try {
                    $docs = app(\App\Services\DocumentoService::class);

                    // ¿Aplicar a todas las variantes del mismo servicio?
                    if (!empty($servicio['servicio_id']) && $this->aplicar_a_todas) {
                        // 1) Subir una vez y obtener meta
                        $meta = $docs->subirYDevolverMeta(
                            $this->archivo_diseno,
                            "servicios/{$this->pedido_id}/{$servicio['servicio_id']}"
                        );

                        // 2) Obtener todas las variantes de ese servicio en este pedido
                        $psvIds = \App\Models\PedidoServicioVariante::where('pedido_id', $this->pedido_id)
                            ->where('servicio_id', $servicio['servicio_id'])
                            ->pluck('id')
                            ->all();

                        if (!empty($psvIds)) {
                            // 3) Crear un registro por variante reutilizando la misma meta
                            $docs->crearRegistrosVarianteDesdeMeta($psvIds, 'archivo_diseno', $meta);

                            // (Opcional) actualizar nombre visible en todas
                            \App\Models\PedidoServicioVariante::whereIn('id', $psvIds)
                                ->update(['nota_disenio' => $servicio['archivo_diseno_nombre']]);

                            $this->toastJs('success', 'Diseño aplicado a todas las variantes.');

                        } else {
                            $this->toastJs('warning', 'No hay variantes para este servicio en el pedido.');
                        }
                    } else {
                        // Sólo esta variante
                        $docs->subirParaVariante((int)$servicio['psv_id'], 'archivo_diseno', $this->archivo_diseno);

                        // (Opcional) nombre visible en esta variante
                        \App\Models\PedidoServicioVariante::whereKey($servicio['psv_id'])
                            ->update(['nota_disenio' => $servicio['archivo_diseno_nombre']]);

                        $this->toastJs('success', 'Diseño subido a la variante.');

                    }

                    // Limpieza del file temporal del input y del check
                    $this->archivo_diseno   = null;
                    $this->aplicar_a_todas  = false;

                } catch (\Throwable $e) {
                    $this->toastJs('error', 'No se pudo subir el archivo. Intenta nuevamente.');
                    return;
                }
            }
        }

        // Insumos según tipo
        if ($tipo === 'personalizado') {
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
            $insumos_usados = [];
            foreach ($this->insumos_con_variantes as $insumo) {
                if (!empty($insumo['variantes_seleccionadas']) && is_array($insumo['variantes_seleccionadas'])) {
                    foreach ($insumo['variantes_seleccionadas'] as $variante_id) {
                        $variante = \App\Models\VarianteInsumo::find($variante_id);
                        if ($variante) {
                            $insumos_usados[] = [
                                'insumo_id'   => $insumo['id'],
                                'nombre'      => $insumo['nombre'],
                                'variante_id' => $variante->id,
                                'atributos'   => is_string($variante->atributos)
                                    ? json_decode($variante->atributos, true)
                                    : $variante->atributos,
                            ];
                        }
                    }
                }
            }
            $servicio['insumos_usados'] = $insumos_usados;
        }

        // Cierra modal, limpia helpers y recalcula totales
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
            $this->dispatch('toast', ['type' => 'warning', 'msg' => 'Primero guarda el pedido para adjuntar archivos.']);
            return;
        }

        $this->validate([
            'archivo_comprobante' => 'required|file|max:102400|mimetypes:application/pdf,application/vnd.corel-draw,application/octet-stream',
        ]);

        $docs = app(DocumentoService::class);
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

        $docs = app(\App\Services\DocumentoService::class);
        $docs->subirParaVariante($psvId, 'archivo_diseno', $this->archivo_diseno);

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





}


