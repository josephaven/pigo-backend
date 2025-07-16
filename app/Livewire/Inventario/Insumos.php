<?php

namespace App\Livewire\Inventario;

use App\Models\CategoriaInsumo;
use App\Models\Insumo;
use App\Models\Sucursal;
use App\Models\VarianteInsumo;
use App\Models\StockSucursal;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Insumos extends Component
{
    public $nombre, $descripcion, $unidad_medida, $categoria_insumo_id;
    public $tiene_variantes = false;
    public $variantes = []; // combinaciones
    public $stock_minimo = 0;

    public $stockInicial = []; // stock sin variantes
    public $stockPorVariante = []; // stock con variantes: varianteIndex => [sucursal_id => cantidad]

    public $modal_abierto = false;
    public $modo_edicion = false;
    public $insumo_id;

    public $filtroKey;
    public $filtro_nombre = '';
    public $filtro_categoria = '';
    public $filtro_alerta = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'unidad_medida' => 'required|string|max:50',
        'categoria_insumo_id' => 'required|exists:categoria_insumos,id',
        'tiene_variantes' => 'boolean',
        'stock_minimo' => 'nullable|numeric|min:0',
        'variantes' => 'nullable|array',
        'stockInicial' => 'nullable|array',
        'stockPorVariante' => 'nullable|array',
    ];

    protected $listeners = ['abrirModalExterno' => 'abrirModal'];

    public function render()
    {
        $insumos = Insumo::with('categoria')
            ->when($this->filtro_nombre, fn($q) => $q->where('nombre', 'like', '%' . $this->filtro_nombre . '%'))
            ->when($this->filtro_categoria, fn($q) => $q->where('categoria_insumo_id', $this->filtro_categoria))
            ->get();

        return view('livewire.inventario.insumos', [
            'insumos' => $insumos,
            'categorias' => CategoriaInsumo::all(),
            'sucursales' => Sucursal::all(),
        ])->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->reset([
            'nombre', 'descripcion', 'unidad_medida', 'categoria_insumo_id',
            'tiene_variantes', 'stock_minimo', 'variantes',
            'stockInicial', 'stockPorVariante',
            'modo_edicion', 'insumo_id'
        ]);

        $this->modal_abierto = true;
    }

    public function editar($id)
    {
        $insumo = Insumo::findOrFail($id);

        $this->insumo_id = $id;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->unidad_medida = $insumo->unidad_medida;
        $this->categoria_insumo_id = $insumo->categoria_insumo_id;
        $this->tiene_variantes = $insumo->tiene_variantes;
        $this->modo_edicion = true;
        $this->modal_abierto = true;

        // No cargamos variantes ni stock para edición aún (opcional si se desea)
    }

    public function guardar()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $insumo = Insumo::updateOrCreate(
                ['id' => $this->modo_edicion ? $this->insumo_id : null],
                [
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'unidad_medida' => $this->unidad_medida,
                    'categoria_insumo_id' => $this->categoria_insumo_id,
                    'tiene_variantes' => $this->tiene_variantes,
                ]
            );

            // Borrar variantes previas si estamos editando
            if ($this->modo_edicion) {
                VarianteInsumo::where('insumo_id', $insumo->id)->delete();
                StockSucursal::where('insumo_id', $insumo->id)->delete();
                StockSucursal::whereIn('variante_insumo_id', function ($q) use ($insumo) {
                    $q->select('id')->from('variantes_insumos')->where('insumo_id', $insumo->id);
                })->delete();
            }

            if ($this->tiene_variantes && !empty($this->variantes)) {
                foreach ($this->variantes as $i => $atributos) {
                    $variante = VarianteInsumo::create([
                        'insumo_id' => $insumo->id,
                        'atributos' => json_encode($atributos),
                    ]);

                    foreach ($this->stockPorVariante[$i] ?? [] as $sucursal_id => $cantidad) {
                        StockSucursal::create([
                            'sucursal_id' => $sucursal_id,
                            'variante_insumo_id' => $variante->id,
                            'cantidad_actual' => $cantidad ?? 0,
                            'stock_minimo' => 0,
                        ]);
                    }
                }
            } else {
                foreach ($this->stockInicial ?? [] as $sucursal_id => $cantidad) {
                    StockSucursal::create([
                        'sucursal_id' => $sucursal_id,
                        'insumo_id' => $insumo->id,
                        'cantidad_actual' => $cantidad ?? 0,
                        'stock_minimo' => $this->stock_minimo ?? 0,
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('toast', mensaje: $this->modo_edicion ? 'Insumo actualizado' : 'Insumo creado');
            $this->cerrarModal();

        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Error al guardar insumo: ' . $e->getMessage());
            $this->dispatch('toast', mensaje: 'Error al guardar insumo', tipo: 'error');
        }
    }

    public function cerrarModal()
    {
        $this->reset([
            'modal_abierto', 'modo_edicion', 'insumo_id',
            'nombre', 'descripcion', 'unidad_medida', 'categoria_insumo_id',
            'tiene_variantes', 'stock_minimo', 'variantes',
            'stockInicial', 'stockPorVariante',
        ]);

        $this->filtroKey = now(); // Forzar re-render si es necesario
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtro_nombre', 'filtro_categoria', 'filtro_alerta']);
        $this->filtroKey = now();
    }
}
