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
    }

    public function render()
    {
        $this->recalcularTotal();
        return view('livewire.pedidos.nuevo-pedido', [
            'sucursales' => Sucursal::all(),
        ])->layout('layouts.app');
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

    public function cargarPedido($id)
    {
        $pedido = Pedido::findOrFail($id);

        $this->pedido_id = $pedido->id;
        $this->cliente_id = $pedido->cliente_id;
        $this->sucursal_entrega_id = $pedido->sucursal_entrega_id;
        $this->sucursal_elaboracion_id = $pedido->sucursal_elaboracion_id;
        $this->fecha_entrega = $pedido->fecha_entrega;
        $this->anticipo = $pedido->anticipo;
        $this->total = $pedido->total;
        $this->justificacion_precio = $pedido->justificacion_precio;
        $this->metodo_pago_id = $pedido->metodo_pago_id;

        // Precargar datos fiscales si el pedido tiene factura
        $factura = FacturaPedido::where('pedido_id', $pedido->id)->first();
        if ($factura) {
            $this->requiere_factura = true;
            $this->rfc = $factura->rfc;
            $this->razon_social = $factura->razon_social;
            $this->direccion_fiscal = $factura->direccion;
            $this->uso_cfdi = $factura->uso_cfdi;
            $this->metodo_pago_factura = $factura->metodo_pago;
        } else {
            // Limpia los campos en caso de no tener factura
            $this->requiere_factura = false;
            $this->rfc = $this->razon_social = $this->direccion_fiscal = $this->uso_cfdi = $this->metodo_pago_factura = '';
        }
    }


    public function guardar()
    {
        // Registro de cliente nuevo
        if ($this->cliente_nuevo) {
            $this->validate([
                'nombre_cliente' => 'required|string|max:255',
                'telefono_cliente' => ['required', Rule::unique('clientes', 'telefono')],
                'tipo_cliente' => ['required', 'in:Normal,Frecuente,Maquilador'],
                'ocupacion_cliente' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date|before:today',
            ]);

            $nuevo = Cliente::create([
                'nombre_completo' => $this->nombre_cliente,
                'telefono' => $this->telefono_cliente,
                'tipo_cliente' => $this->tipo_cliente,
                'ocupacion' => $this->ocupacion_cliente,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sucursal_id' => $this->sucursal_registro_id,
            ]);

            $this->cliente_id = $nuevo->id;
            $this->cliente_nuevo = false;
            $this->dispatch('toast', 'Cliente nuevo registrado correctamente');
        }

        // Validación final del pedido
        $this->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')],
            'sucursal_entrega_id' => ['required', 'different:sucursal_elaboracion_id'],
            'sucursal_elaboracion_id' => ['required'],
            'fecha_entrega' => ['required', 'date', 'after_or_equal:today'],
            'anticipo' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'metodo_pago_id' => ['required', Rule::exists('metodos_pago', 'id')],
        ]);

        if ($this->requiere_factura) {
            $this->validate([
                'rfc' => 'required|string|max:13',
                'razon_social' => 'required|string|max:255',
                'direccion_fiscal' => 'required|string|max:255',
                'uso_cfdi' => 'nullable|string|max:50',
                'metodo_pago_factura' => 'nullable|string|max:100',
            ]);
        }

        $data = [
            'cliente_id' => $this->cliente_id,
            'sucursal_registro_id' => $this->sucursal_registro_id,
            'sucursal_entrega_id' => $this->sucursal_entrega_id,
            'sucursal_elaboracion_id' => $this->sucursal_elaboracion_id,
            'fecha_entrega' => $this->fecha_entrega,
            'anticipo' => $this->anticipo,
            'total' => $this->total,
            'justificacion_precio' => $this->justificacion_precio,
            'metodo_pago_id' => $this->metodo_pago_id,
            'user_id' => Auth::id(),
        ];

        if ($this->modo_edicion && $this->pedido_id) {
            Pedido::findOrFail($this->pedido_id)->update($data);
            session()->flash('mensaje', 'Pedido actualizado correctamente');
        } else {
            $pedido = Pedido::create($data);
            $this->pedido_id = $pedido->id;

            // Crear factura si aplica
            if ($this->requiere_factura) {
                FacturaPedido::create([
                    'pedido_id' => $pedido->id,
                    'rfc' => $this->rfc,
                    'razon_social' => $this->razon_social,
                    'direccion' => $this->direccion_fiscal,
                    'uso_cfdi' => $this->uso_cfdi,
                    'metodo_pago' => $this->metodo_pago_factura,
                ]);
            }

            session()->flash('mensaje', 'Pedido creado correctamente');
        }

        return redirect()->route('pedidos');
    }

    public function cargarServicioSeleccionado()
    {
        if (!$this->servicio_seleccionado_id) return;

        $servicio = Servicio::with(['camposPersonalizados', 'insumos.variantes'])->find($this->servicio_seleccionado_id);

        if (!$servicio) return;

        $campos = $servicio->camposPersonalizados->map(function ($campo) {
            return [
                'id' => $campo->id,
                'nombre' => $campo->nombre,
                'tipo' => $campo->tipo,
                'valor' => null,
                'opciones' => $campo->opciones ?? [],
            ];
        });

        $insumos = $servicio->insumos->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'variantes' => $insumo->variantes->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'atributos' => is_string($v->atributos) ? json_decode($v->atributos, true) : $v->atributos,

                    ];
                }),
                'variantes_seleccionadas' => []
            ];
        });

        $this->campos_personalizados = $campos->toArray();
        $this->insumos_con_variantes = $insumos->toArray();
    }

    public function agregarServicio()
    {
        $this->validate([
            'servicio_seleccionado_id' => 'required|exists:servicios,id',
        ]);

        foreach ($this->campos_personalizados as $i => $campo) {
            $tipo = $campo['tipo'] ?? 'texto';
            $base = "campos_personalizados.$i.valor";

            switch ($tipo) {
                case 'texto':
                case 'select':
                    $this->validate([
                        $base => 'nullable|string|max:255',
                    ]);
                    break;

                case 'numero':
                    $this->validate([
                        $base => 'nullable|numeric|min:0',
                    ]);
                    break;

                case 'booleano':
                    $this->validate([
                        $base => 'nullable|boolean',
                    ]);
                    break;

                default:
                    // Tipo desconocido, se ignora o se puede lanzar error si quieres
                    break;
            }
        }


        $servicio = Servicio::find($this->servicio_seleccionado_id);

        $insumos_usados = [];

        foreach ($this->insumos_con_variantes as $insumo) {
            if (!empty($insumo['variantes_seleccionadas']) && is_array($insumo['variantes_seleccionadas'])) {
                foreach ($insumo['variantes_seleccionadas'] as $variante_id) {
                    $variante = VarianteInsumo::find($variante_id);
                    if ($variante) {
                        $insumos_usados[] = [
                            'insumo_id' => $insumo['id'],
                            'nombre' => $insumo['nombre'],
                            'variante_id' => $variante->id,
                            'atributos' => is_string($variante->atributos)
                                ? json_decode($variante->atributos, true)
                                : $variante->atributos,
                        ];
                    }
                }
            }
        }

        $nuevo_servicio = [
            'servicio_id' => $servicio->id,
            'nombre' => $servicio->nombre,
            'campos_personalizados' => $this->campos_personalizados,
            'insumos_usados' => $insumos_usados,
            'cantidad' => 1,
            'precio_unitario' => $this->tipo_cliente === 'Maquilador' ? $servicio->precio_maquilador : $servicio->precio_normal,
            'subtotal' => ($this->tipo_cliente === 'Maquilador' ? $servicio->precio_maquilador : $servicio->precio_normal),
            'total_final' => null,
            'justificacion_total' => null,
            'archivo_diseno' => null,
        ];

        if ($this->indice_edicion_servicio !== null) {
            $this->servicios_pedido[$this->indice_edicion_servicio] = $nuevo_servicio;
        } else {
            $this->servicios_pedido[] = $nuevo_servicio;
        }

        // Reset de propiedades
        $this->resetServicio();
    }



    public function eliminarServicio($index)
    {
        unset($this->servicios_pedido[$index]);
        $this->servicios_pedido = array_values($this->servicios_pedido); // reindexar
    }


    public function editarServicio($index)
    {
        $servicio = $this->servicios_pedido[$index] ?? null;

        if (!$servicio) return;

        $this->indice_edicion_servicio = $index;
        $this->servicio_seleccionado_id = $servicio['servicio_id'];
        $this->campos_personalizados = $servicio['campos_personalizados'] ?? [];
        $this->insumos_con_variantes = [];

        $servicio_base = Servicio::with('insumos.variantes')->find($this->servicio_seleccionado_id);

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

        // Validar archivo si se subió uno
        $this->validate([
            'archivo_diseno' => 'nullable|file|max:102400|mimes:jpg,jpeg,png,pdf,ai,svg,eps',
        ]);

        $subtotal = ($this->servicios_pedido[$this->indice_edicion_servicio]['cantidad'] ?? 1)
            * $this->servicios_pedido[$this->indice_edicion_servicio]['precio_unitario'];

        // Guardar campos personalizados
        $this->servicios_pedido[$this->indice_edicion_servicio]['campos_personalizados'] = $this->campos_personalizados;

        // Guardar archivo y nombre si aplica
        if ($this->archivo_diseno) {
            $this->servicios_pedido[$this->indice_edicion_servicio]['archivo_diseno'] = $this->archivo_diseno;
            $this->servicios_pedido[$this->indice_edicion_servicio]['archivo_diseno_nombre'] = $this->archivo_diseno->getClientOriginalName();
        }

        // Guardar subtotal
        $this->servicios_pedido[$this->indice_edicion_servicio]['subtotal'] = $subtotal;

        // Reset modal
        $this->resetServicio();
    }



    public function recalcularTotal()
    {
        $this->total = collect($this->servicios_pedido)->sum(function ($servicio) {
            return ($servicio['subtotal'] ?? 0);
        });
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




}
