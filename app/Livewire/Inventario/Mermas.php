<?php

namespace App\Livewire\Inventario;

use App\Models\Merma;
use App\Models\Insumo;
use App\Models\Sucursal;
use Livewire\Component;
use App\Models\VarianteInsumo;
use Illuminate\Support\Facades\Auth;
use App\Models\StockSucursal;
use App\Models\MovimientoInsumo;

class Mermas extends Component
{
    public $mermas;
    public $modal_abierto = false;
    public $modo_edicion = false;

    public $merma_id;

    public $insumo_id, $variante_insumo_id, $cantidad, $justificacion, $fecha;

    public $insumos = [];
    public $variantes = [];

    public $filtro_fecha = '';
    public $filtroKey;

    public $insumoBuscado = '';
    public $insumosDisponibles = [];
    public $insumosSeleccionados = [];
    public $cantidadesMerma = [];

    protected $rules = [
        'fecha' => 'required|date',
        'justificacion' => 'nullable|string|max:255',
    ];


    protected $listeners = [
        'abrirModalExterno' => 'abrirModal',
    ];

    public function mount()
    {
        $this->insumos = Insumo::orderBy('nombre')->get();
        $this->fecha = now()->toDateString();
        $this->filtroKey = uniqid();
        $this->cargarMermas();
    }

    public function cargarMermas()
    {
        $query = Merma::with(['insumo', 'variante', 'user'])
            ->where('sucursal_id', sucursal_activa_id());

        if ($this->filtro_fecha) {
            $query->whereDate('fecha', $this->filtro_fecha);
        }

        $this->mermas = $query->latest()->get();
    }

    public function updatedInsumoId()
    {
        $this->variante_insumo_id = null;
        $insumo = Insumo::find($this->insumo_id);

        if ($insumo && $insumo->tiene_variantes) {
            $this->variantes = VarianteInsumo::where('insumo_id', $insumo->id)->get();
        } else {
            $this->variantes = [];
        }
    }

    public function abrirModal()
    {
        $this->reset([
            'insumoBuscado', 'insumosDisponibles',
            'insumosSeleccionados', 'cantidadesMerma',
            'justificacion', 'fecha', 'modo_edicion', 'merma_id'
        ]);
        $this->fecha = now()->toDateString();
        $this->modal_abierto = true;
    }


    public function cancelar()
    {
        $this->reset(['modal_abierto', 'modo_edicion', 'merma_id', 'justificacion']);
    }


    public function guardar()
    {
        // 🟡 Si estamos editando una merma existente (solo se permite cambiar justificación)
        if ($this->modo_edicion) {
            $this->validate([
                'justificacion' => 'nullable|string|max:255',
            ]);

            try {
                $merma = Merma::findOrFail($this->merma_id);
                $merma->justificacion = $this->justificacion;
                $merma->save();

                $this->modal_abierto = false;
                $this->reset(['modo_edicion', 'merma_id', 'justificacion']);
                $this->cargarMermas();

                $this->dispatch('toast', mensaje: 'Justificación actualizada correctamente', tipo: 'info');
            } catch (\Throwable $e) {
                logger()->error('Error al editar justificación: ' . $e->getMessage());
                $this->dispatch('toast', mensaje: 'Error al editar la justificación', tipo: 'error');
            }

            return;
        }

        // 🟢 Registro normal de merma
        $sucursal_id = sucursal_activa_id();
        $user_id = Auth::id();

        $validos = collect($this->cantidadesMerma)->filter(fn($v) => $v > 0);

        if ($validos->isEmpty()) {
            $this->dispatch('toast', mensaje: 'Debes ingresar al menos un insumo con cantidad mayor a 0.', tipo: 'error');
            return;
        }

        \DB::beginTransaction();

        try {
            foreach ($this->cantidadesMerma as $clave => $cantidad) {
                if ($cantidad <= 0) continue;

                [$tipo, $id] = explode('-', $clave);

                $stock = StockSucursal::where('sucursal_id', $sucursal_id)
                    ->where($tipo === 'insumo' ? 'insumo_id' : 'variante_insumo_id', $id)
                    ->first();

                if (!$stock || $stock->cantidad_actual < $cantidad) {
                    $this->addError('cantidadesMerma.' . $clave, 'Stock insuficiente para este insumo.');
                    continue;
                }

                // Descontar stock
                $stock->cantidad_actual -= $cantidad;
                $stock->save();

                // Registrar merma
                Merma::create([
                    'sucursal_id' => $sucursal_id,
                    'user_id' => $user_id,
                    'insumo_id' => $tipo === 'insumo' ? $id : null,
                    'variante_insumo_id' => $tipo === 'variante' ? $id : null,
                    'cantidad' => $cantidad,
                    'justificacion' => $this->justificacion,
                    'fecha' => $this->fecha,
                ]);

                // Registrar movimiento
                MovimientoInsumo::create([
                    'sucursal_id' => $sucursal_id,
                    'user_id' => $user_id,
                    'insumo_id' => $tipo === 'insumo' ? $id : null,
                    'variante_insumo_id' => $tipo === 'variante' ? $id : null,
                    'tipo' => 'merma',
                    'cantidad' => $cantidad,
                    'origen' => 'Merma manual',
                    'motivo' => $this->justificacion,
                    'fecha' => $this->fecha,
                ]);
            }

            \DB::commit();

            $this->modal_abierto = false;
            $this->reset([
                'cantidadesMerma', 'insumosSeleccionados',
                'insumoBuscado', 'insumosDisponibles',
                'justificacion', 'fecha'
            ]);

            $this->cargarMermas();

            $this->dispatch('toast', mensaje: 'Mermas registradas correctamente', tipo: 'success');

        } catch (\Throwable $e) {
            \DB::rollBack();
            logger()->error('Error al registrar mermas: ' . $e->getMessage());

            $this->dispatch('toast', mensaje: 'Error al registrar las mermas', tipo: 'error');
        }
    }




    public function limpiarFiltros()
    {
        $this->reset(['filtro_fecha']);
        $this->filtroKey = uniqid();
        $this->cargarMermas();
    }

    public function updatedInsumoBuscado()
    {
        $this->buscarInsumos();
    }

    public function buscarInsumos()
    {
        if (strlen($this->insumoBuscado) < 2) {
            $this->insumosDisponibles = [];
            return;
        }

        $sucursalId = sucursal_activa_id();

        $insumos = Insumo::with([
            'variantes.stockSucursales' => fn($q) =>
            $q->where('sucursal_id', $sucursalId),
            'stockSucursales' => fn($q) =>
            $q->where('sucursal_id', $sucursalId),
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

    public function agregarInsumo($tipo, $id)
    {
        $clave = $tipo . '-' . $id;

        if (!array_key_exists($clave, $this->cantidadesMerma)) {
            $this->cantidadesMerma[$clave] = null;

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
        unset($this->cantidadesMerma[$clave]);
        unset($this->insumosSeleccionados[$clave]);
    }

    public function editar($id)
    {
        $merma = Merma::findOrFail($id);

        $this->merma_id = $merma->id;
        $this->justificacion = $merma->justificacion;
        $this->modo_edicion = true;
        $this->modal_abierto = true;
    }




    public function render()
    {
        return view('livewire.inventario.mermas')->layout('layouts.app');
    }
}
