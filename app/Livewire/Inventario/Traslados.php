<?php

namespace App\Livewire\Inventario;

use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\TrasladoInsumo;
use App\Models\DetalleTraslado;
use App\Models\VarianteInsumo;
use App\Models\StockSucursal;
use App\Models\MovimientoInsumo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Traslados extends Component
{
    public $sucursal_origen_id;
    public $sucursal_destino_id;
    public $cantidadesTraslado = [];
    public $modal_abierto = false;

    public $sucursales;
    public $modal_detalles_abierto = false;
    public $trasladoSeleccionado;
    public $estado_editando_id = null;
    public $insumoBuscado = '';
    public $insumosDisponibles = []; // resultados de búsqueda
    public $insumosSeleccionados = []; // insumos ya agregados al traslado
    public $filtro_origen = '';
    public $filtro_destino = '';
    public $filtro_estado = '';
    public $filtro_usuario = '';
    public $filtroKey;

    public $filtro_fecha = '';






    protected $listeners = [
        'abrirModalExterno' => 'abrirModal',
    ];


    public function mount()
    {
        $this->sucursal_origen_id = sucursal_activa_id();
        $this->sucursales = Sucursal::all();
        $this->filtroKey = uniqid();
    }

    public function abrirModal()
    {
        $this->reset(['sucursal_destino_id', 'cantidadesTraslado']);
        $this->modal_abierto = true;
    }

    public function cerrarModal()
    {
        $this->reset(['modal_abierto', 'sucursal_destino_id', 'cantidadesTraslado']);
    }

    public function updatedSucursalOrigenId()
    {
        $this->cantidadesTraslado = [];
    }

    public function guardar()
    {
        logger('🧪 MÉTODO GUARDAR EJECUTADO');
        $this->validate([
            'sucursal_origen_id' => 'required|different:sucursal_destino_id',
            'sucursal_destino_id' => 'required',
        ]);

        if (collect($this->cantidadesTraslado)->filter(fn($v) => $v > 0)->isEmpty()) {
            $this->dispatch('toast', mensaje: 'Debes ingresar al menos un insumo con cantidad mayor a 0.', tipo: 'error');
            return;
        }

        DB::beginTransaction();

        try {
            $traslado = TrasladoInsumo::create([
                'sucursal_origen_id' => $this->sucursal_origen_id,
                'sucursal_destino_id' => $this->sucursal_destino_id,
                'user_id' => Auth::id(),
                'fecha_solicitud' => now(),
                'estado' => 'pendiente',
            ]);

            foreach ($this->cantidadesTraslado as $clave => $cantidad) {
                if ($cantidad <= 0) continue;

                [$tipo, $id] = explode('-', $clave);

                DetalleTraslado::create([
                    'traslado_insumo_id' => $traslado->id,
                    'insumo_id' => $tipo === 'insumo' ? $id : null,
                    'variante_insumo_id' => $tipo === 'variante' ? $id : null,
                    'cantidad' => $cantidad,
                ]);

                $stock = StockSucursal::where('sucursal_id', $this->sucursal_origen_id)
                    ->where($tipo === 'insumo' ? 'insumo_id' : 'variante_insumo_id', $id)
                    ->first();

                if ($stock) {
                    $stock->cantidad_actual -= $cantidad;
                    $stock->save();
                }

                MovimientoInsumo::create([
                    'insumo_id' => $tipo === 'insumo' ? $id : null,
                    'variante_insumo_id' => $tipo === 'variante' ? $id : null,
                    'sucursal_id' => $this->sucursal_origen_id,
                    'user_id' => Auth::id(),
                    'tipo' => 'salida',
                    'cantidad' => $cantidad,
                    'origen' => 'Traslado a sucursal ID ' . $this->sucursal_destino_id,
                    'motivo' => 'Salida por traslado',
                    'fecha' => now(),
                ]);
            }

            DB::commit();

            $this->dispatch('toast', mensaje: 'Traslado registrado correctamente', tipo: 'success');
            $this->cerrarModal();
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Error al registrar traslado: ' . $e->getMessage());
            $this->dispatch('toast', mensaje: 'Error al registrar traslado', tipo: 'error');
        }
    }

    public function actualizarEstado($traslado_id, $nuevo_estado)
    {
        $traslado = TrasladoInsumo::with('detalles')->findOrFail($traslado_id);
        $rolUsuario = auth()->user()->rol->nombre ?? null;

        if ($nuevo_estado === 'recibido' && !in_array($rolUsuario, ['Jefe', 'Gerente'])) {
            $this->dispatch('toast', mensaje: 'No tienes permiso para marcar como recibido.', tipo: 'error');
            return;
        }

        // Solo si estaba pendiente y pasa a recibido
        if ($traslado->estado === 'pendiente' && $nuevo_estado === 'recibido') {
            foreach ($traslado->detalles as $detalle) {
                // Detectar si es insumo o variante
                $campo = $detalle->variante_insumo_id ? 'variante_insumo_id' : 'insumo_id';
                $id = $detalle->$campo;

                // Buscar o crear stock en sucursal destino
                $stock = StockSucursal::firstOrCreate(
                    [
                        'sucursal_id' => $traslado->sucursal_destino_id,
                        $campo => $id,
                    ],
                    ['cantidad_actual' => 0]
                );

                $stock->cantidad_actual += $detalle->cantidad;
                $stock->save();

                // Registrar movimiento de entrada
                MovimientoInsumo::create([
                    'insumo_id' => $detalle->insumo_id,
                    'variante_insumo_id' => $detalle->variante_insumo_id,
                    'sucursal_id' => $traslado->sucursal_destino_id,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada',
                    'cantidad' => $detalle->cantidad,
                    'origen' => 'Traslado recibido desde sucursal ID ' . $traslado->sucursal_origen_id,
                    'motivo' => 'Entrada por traslado',
                    'fecha' => now(),
                ]);
            }
        }

        // 🔴 Cancelado → Revertir stock en origen
        if ($traslado->estado === 'pendiente' && $nuevo_estado === 'cancelado') {
            foreach ($traslado->detalles as $detalle) {
                $campo = $detalle->variante_insumo_id ? 'variante_insumo_id' : 'insumo_id';
                $id = $detalle->$campo;

                $stock = StockSucursal::firstOrCreate(
                    [
                        'sucursal_id' => $traslado->sucursal_origen_id,
                        $campo => $id,
                    ],
                    ['cantidad_actual' => 0]
                );

                $stock->cantidad_actual += $detalle->cantidad;
                $stock->save();

                MovimientoInsumo::create([
                    'insumo_id' => $detalle->insumo_id,
                    'variante_insumo_id' => $detalle->variante_insumo_id,
                    'sucursal_id' => $traslado->sucursal_origen_id,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada',
                    'cantidad' => $detalle->cantidad,
                    'origen' => 'Cancelación de traslado ID ' . $traslado->id,
                    'motivo' => 'Reverso por cancelación',
                    'fecha' => now(),
                ]);
            }
        }

        // Actualizar el estado final
        $traslado->estado = $nuevo_estado;
        $traslado->save();

        $this->dispatch('toast', mensaje: 'Estado actualizado correctamente.', tipo: 'success');
    }


    public function verDetalles($traslado_id)
    {
        $this->trasladoSeleccionado = TrasladoInsumo::with([
            'detalles.insumo', 'detalles.varianteInsumo', 'sucursalOrigen', 'sucursalDestino', 'user'
        ])->findOrFail($traslado_id);

        $this->modal_detalles_abierto = true;
    }

    public function render()
    {
        $insumos = [];

        if ($this->sucursal_origen_id) {
            $insumos = Insumo::with([
                'categoria',
                'variantes.stockSucursales' => fn($q) => $q->where('sucursal_id', $this->sucursal_origen_id),
                'stockSucursales' => fn($q) => $q->where('sucursal_id', $this->sucursal_origen_id),
            ])
                ->where(function ($q) {
                    $q->whereHas('stockSucursales', fn($q) => $q->where('sucursal_id', $this->sucursal_origen_id))
                        ->orWhereHas('variantes.stockSucursales', fn($q) => $q->where('sucursal_id', $this->sucursal_origen_id));
                })
                ->get();

            foreach ($insumos as $insumo) {
                if ($insumo->tiene_variantes) {
                    foreach ($insumo->variantes as $variante) {
                        $variante->atributos = json_decode($variante->atributos, true);
                    }
                }
            }
        }

        return view('livewire.inventario.traslados', [
            'sucursales' => $this->sucursales,
            'insumos' => $insumos,
            'traslados' => $this->filtrar(),
        ])->layout('layouts.app');
    }


    public function updatedInsumoBuscado()
    {
        $this->buscarInsumos(); // Para permitir búsqueda automática tras el debounce
    }



    public function agregarInsumo($tipo, $id)
    {
        $clave = $tipo . '-' . $id;

        if (!array_key_exists($clave, $this->cantidadesTraslado)) {
            $this->cantidadesTraslado[$clave] = null;
            $this->insumosSeleccionados[$clave] = collect($this->insumosDisponibles)
                ->flatMap(fn($i) => $i['tiene_variantes']
                    ? collect($i['variantes'])->mapWithKeys(fn($v) => ['variante-' . $v['id'] => [
                        'nombre' => $i['nombre'],
                        'atributos' => $v['atributos'],
                        'stock' => $v['stock'],
                    ]])
                    : ['insumo-' . $i['id'] => [
                        'nombre' => $i['nombre'],
                        'stock' => $i['stock']
                    ]]
                )->get($clave);
        }

        $this->insumoBuscado = '';
        $this->insumosDisponibles = [];
    }

    public function quitarInsumo($clave)
    {
        unset($this->cantidadesTraslado[$clave]);
        unset($this->insumosSeleccionados[$clave]);
    }

    public function buscarInsumos()
    {
        if (strlen($this->insumoBuscado) < 2) {
            $this->insumosDisponibles = [];
            return;
        }

        $insumos = Insumo::with([
            'variantes.stockSucursales' => fn($q) =>
            $q->where('sucursal_id', $this->sucursal_origen_id),
            'stockSucursales' => fn($q) =>
            $q->where('sucursal_id', $this->sucursal_origen_id),
        ])
            ->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ['%' . $this->insumoBuscado . '%'])
            ->take(10)
            ->get();

        $this->insumosDisponibles = $insumos->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'tiene_variantes' => $insumo->tiene_variantes,
                'variantes' => $insumo->tiene_variantes ? $insumo->variantes->map(fn($v) => [
                    'id' => $v->id,
                    'atributos' => json_decode($v->atributos ?? '{}', true) ?? [],
                    'stock' => optional($v->stockSucursales->first())->cantidad_actual ?? 0,
                ]) : [],
                'stock' => optional($insumo->stockSucursales->first())->cantidad_actual ?? 0,
            ];
        })->toArray();
    }

    public function filtrar()
    {
        $query = TrasladoInsumo::with([
            'sucursalOrigen',
            'sucursalDestino',
            'user',
            'detalles'
        ]);

        if ($this->filtro_origen) {
            $query->where('sucursal_origen_id', $this->filtro_origen);
        }

        if ($this->filtro_destino) {
            $query->where('sucursal_destino_id', $this->filtro_destino);
        }

        if ($this->filtro_estado) {
            $query->where('estado', $this->filtro_estado);
        }

        if ($this->filtro_usuario) {
            $query->whereHas('user', fn($q) =>
            $q->where('name', 'ILIKE', '%' . $this->filtro_usuario . '%')
            );
        }

        if ($this->filtro_fecha) {
            $query->whereDate('fecha_solicitud', $this->filtro_fecha);
        }



        return $query->orderByDesc('created_at')->paginate(10);
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtro_origen', 'filtro_destino', 'filtro_estado', 'filtro_usuario', 'filtro_fecha']);
        $this->filtroKey = uniqid(); // fuerza el reinicio visual de los selects/inputs
    }





}
