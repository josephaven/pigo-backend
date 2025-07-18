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


    public $stockInicial = [];
    public $stockPorVariante = [];

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
        'stockInicial' => 'nullable|array',
        'stockPorVariante' => 'nullable|array',
        'atributos' => 'nullable|array',
        'valoresAtributos' => 'nullable|array',
        'combinaciones' => 'nullable|array',

    ];

    protected $listeners = [
        'abrirModalExterno' => 'abrirModal',
        ];

    public $atributos = []; // Ej: ['Talla', 'Color']
    public $valoresAtributos = []; // Ej: ['Talla' => ['S', 'M'], 'Color' => ['Rojo', 'Azul']]
    public $combinaciones = []; // Combinaciones generadas dinámicamente
    public $stockMinimoPorSucursal = [];
    public $stockMinimoPorVariante = []; // [index][sucursal_id] = min



    public function render()
    {
        $insumos = Insumo::with(['categoria', 'stockSucursales'])
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
            'tiene_variantes',
            'stockInicial', 'stockPorVariante',
            'stockMinimoPorSucursal',
            'stockMinimoPorVariante',
            'modo_edicion', 'insumo_id'
        ]);

        $this->modal_abierto = true;
        $this->filtroKey = now();

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
        if (!$insumo->tiene_variantes) {
            $stockSucursales = $insumo->stockSucursales;

            $this->stockInicial = $stockSucursales->pluck('cantidad_actual', 'sucursal_id')->toArray();
            $this->stockMinimoPorSucursal = $stockSucursales->pluck('stock_minimo', 'sucursal_id')->toArray();
        }
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

            if ($this->tiene_variantes && !empty($this->combinaciones)) {
                foreach ($this->combinaciones as $i => $atributos) {
                    $variante = VarianteInsumo::create([
                        'insumo_id' => $insumo->id,
                        'atributos' => json_encode($atributos),
                    ]);

                    foreach ($this->stockPorVariante[$i] ?? [] as $sucursal_id => $cantidad) {
                        StockSucursal::create([
                            'sucursal_id' => $sucursal_id,
                            'variante_insumo_id' => $variante->id,
                            'cantidad_actual' => $cantidad ?? 0,
                            'stock_minimo' => $this->stockMinimoPorVariante[$i][$sucursal_id] ?? 0,
                        ]);
                    }
                }
            } else {
                foreach ($this->stockInicial ?? [] as $sucursal_id => $cantidad) {
                    StockSucursal::create([
                        'sucursal_id' => $sucursal_id,
                        'insumo_id' => $insumo->id,
                        'cantidad_actual' => $cantidad ?? 0,
                        'stock_minimo' => $this->stockMinimoPorSucursal[$sucursal_id] ?? 0,
                    ]);

                }
            }

            DB::commit();

            $this->dispatch('toast', mensaje: $this->modo_edicion ? 'Insumo actualizado correctamente'
                : 'Insumo creado correctamente', tipo: $this->modo_edicion ? 'info' : 'success');
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
            'tiene_variantes',
            'stockInicial', 'stockPorVariante',
            'stockMinimoPorSucursal',
            'stockMinimoPorVariante',
            'atributos', 'valoresAtributos', 'combinaciones',
        ]);

        $this->filtroKey = now();
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtro_nombre', 'filtro_categoria', 'filtro_alerta']);
        $this->filtroKey = now();
    }



    public function updatedTieneVariantes($value)
    {
        if ($value && empty($this->atributos)) {
            $this->atributos[] = 'Atributo 1';
            $this->valoresAtributos['Atributo 1'] = [''];
        }

        if (!$value) {
            $this->atributos = [];
            $this->valoresAtributos = [];
            $this->combinaciones = [];
        }
    }



    public function generarCombinaciones()
    {
        if (empty($this->valoresAtributos)) return;

        // Verificamos que todos los atributos tengan al menos un valor
        foreach ($this->atributos as $atributo) {
            if (empty($this->valoresAtributos[$atributo]) || count($this->valoresAtributos[$atributo]) === 0) {
                $this->combinaciones = [];
                return;
            }
        }

        $listas = array_values($this->valoresAtributos);
        $resultado = [[]];

        foreach ($listas as $atributos) {
            $temp = [];
            foreach ($resultado as $res) {
                foreach ($atributos as $atributo) {
                    $temp[] = array_merge($res, [$atributo]);
                }
            }
            $resultado = $temp;
        }

        $this->combinaciones = [];
        foreach ($resultado as $combo) {
            // Validamos que la cantidad de atributos coincida con el combo
            if (count($this->atributos) === count($combo)) {
                $atributos = array_combine($this->atributos, $combo);
                $this->combinaciones[] = $atributos;
            }
        }
    }


    public function agregarAtributo()
    {
        $nuevo = 'Atributo ' . (count($this->atributos) + 1);
        $this->atributos[] = $nuevo;
        $this->valoresAtributos[$nuevo] = [''];
        $this->generarCombinaciones();
    }


    public function agregarValor($atributo)
    {
        if (!isset($this->valoresAtributos[$atributo])) {
            $this->valoresAtributos[$atributo] = [];
        }

        $this->valoresAtributos[$atributo][] = '';
    }

    public function eliminarValor($atributo, $index)
    {
        if (isset($this->valoresAtributos[$atributo][$index])) {
            unset($this->valoresAtributos[$atributo][$index]);
            $this->valoresAtributos[$atributo] = array_values($this->valoresAtributos[$atributo]);
            $this->generarCombinaciones();// reindexar
        }
    }


    public function updatedValoresAtributos()
    {
        $this->generarCombinaciones();
    }

    public function updatedAtributos()
    {
        $atributosLimpios = array_filter($this->atributos); // elimina vacíos

        // Eliminar claves obsoletas en valores
        foreach ($this->valoresAtributos as $clave => $valores) {
            if (!in_array($clave, $atributosLimpios)) {
                unset($this->valoresAtributos[$clave]);
            }
        }

        // Asegurarse de que cada atributo tenga arreglo
        foreach ($atributosLimpios as $clave) {
            if (!isset($this->valoresAtributos[$clave])) {
                $this->valoresAtributos[$clave] = [];
            }
        }

        $this->atributos = array_values($atributosLimpios); // reindexar
        $this->generarCombinaciones();
    }

    public function eliminarAtributo($index)
    {
        $atributoAEliminar = $this->atributos[$index] ?? null;

        if ($atributoAEliminar) {
            // Eliminar nombre del atributo
            unset($this->atributos[$index]);
            $this->atributos = array_values($this->atributos); // Reindexar

            // Eliminar valores asociados
            unset($this->valoresAtributos[$atributoAEliminar]);

            // Regenerar valoresAtributos con claves actualizadas
            $valoresReasignados = [];
            foreach ($this->atributos as $atributo) {
                $valoresReasignados[$atributo] = $this->valoresAtributos[$atributo] ?? [''];
            }
            $this->valoresAtributos = $valoresReasignados;

            // Recalcular combinaciones
            $this->generarCombinaciones();
        }
    }



    public function agregarValorPorIndice($index)
    {
        // Asegura que el índice existe y tiene nombre
        if (!isset($this->atributos[$index]) || $this->atributos[$index] === '') return;

        $nombre = $this->atributos[$index];

        // Si el atributo fue renombrado, asegura que la clave en valores exista
        if (!isset($this->valoresAtributos[$nombre])) {
            $this->valoresAtributos[$nombre] = [];
        }

        $this->valoresAtributos[$nombre][] = '';
        $this->generarCombinaciones();
    }

    public function agregarValorPorNombre($nombre)
    {
        if (!isset($this->valoresAtributos[$nombre])) {
            $this->valoresAtributos[$nombre] = [];
        }

        $this->valoresAtributos[$nombre][] = '';
        $this->generarCombinaciones();
    }







}
