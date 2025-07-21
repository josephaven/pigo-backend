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

    protected $listeners = [
        'abrirModalExterno' => 'abrirModal',
    ];

    public function mount()
    {
        $this->sucursal_origen_id = sucursal_activa_id();
        $this->sucursales = Sucursal::all();
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
                    'tipo' => 'traslado_salida',
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
        $traslado = TrasladoInsumo::findOrFail($traslado_id);
        $rolUsuario = auth()->user()->rol->nombre ?? null;

        if ($nuevo_estado === 'recibido' && !in_array($rolUsuario, ['Jefe', 'Gerente'])) {
            $this->dispatch('toast', mensaje: 'No tienes permiso para marcar como recibido.', tipo: 'error');
            return;
        }

        $traslado->estado = $nuevo_estado;
        $traslado->save();

        $this->dispatch('toast', mensaje: 'Estado actualizado', tipo: 'success');
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
            'traslados' => TrasladoInsumo::with([
                'sucursalOrigen',
                'sucursalDestino',
                'user',
                'detalles'
            ])->orderByDesc('created_at')->paginate(10),
        ])->layout('layouts.app');
    }
}
