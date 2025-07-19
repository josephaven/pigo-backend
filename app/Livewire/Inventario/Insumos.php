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
    public $filasConVariantesAbiertas = [];





    public function render()
    {
        $insumos = Insumo::with(['categoria', 'stockSucursales', 'variantes.stockSucursales'])
            ->when($this->filtro_nombre, fn($q) => $q->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->filtro_nombre) . '%']))
            ->when($this->filtro_categoria, fn($q) => $q->where('categoria_insumo_id', $this->filtro_categoria))
            ->get()
            ->filter(function ($insumo) {
                if (!$this->filtro_alerta) return true;

                return strtolower($insumo->alerta_stock) === strtolower($this->filtro_alerta);
            });


        return view('livewire.inventario.insumos', [
            'insumos' => $insumos,
            'categorias' => CategoriaInsumo::all(),
            'sucursales' => Sucursal::all(),
            'unidadesExistentes' => $this->unidadesExistentes,
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

        // Limpiar estados previos
        $this->stockInicial = [];
        $this->stockMinimoPorSucursal = [];
        $this->stockPorVariante = [];
        $this->stockMinimoPorVariante = [];

        if ($insumo->tiene_variantes) {
            $variantes = $insumo->variantes;

            $atributosTemp = [];
            $valoresAtributosTemp = [];
            $combinacionesTemp = [];

            // Primera pasada: recolectar atributos, valores y combinaciones
            foreach ($variantes as $variante) {
                $atributos = is_array($variante->atributos)
                    ? $variante->atributos
                    : json_decode($variante->atributos, true);

                $combinacionesTemp[] = $atributos;

                foreach ($atributos as $nombre => $valor) {
                    if (!in_array($nombre, $atributosTemp)) {
                        $atributosTemp[] = $nombre;
                    }
                    $valoresAtributosTemp[$nombre][] = $valor;
                }
            }

            // Eliminar duplicados en valores
            foreach ($valoresAtributosTemp as $key => $valores) {
                $valoresAtributosTemp[$key] = array_values(array_unique($valores));
            }

            $this->atributos = $atributosTemp;
            $this->valoresAtributos = $valoresAtributosTemp;
            $this->combinaciones = $combinacionesTemp;

            // Segunda pasada: asignar stock a la combinación correspondiente
            foreach ($variantes as $variante) {
                $atributos = is_array($variante->atributos)
                    ? $variante->atributos
                    : json_decode($variante->atributos, true);

                $index = collect($this->combinaciones)->search(function ($combo) use ($atributos) {
                    return collect($combo)->diffAssoc($atributos)->isEmpty()
                        && collect($atributos)->diffAssoc($combo)->isEmpty();
                });

                if ($index === false) continue;

                foreach ($variante->stockSucursales as $stock) {
                    $this->stockPorVariante[$index][$stock->sucursal_id] = $stock->cantidad_actual;
                    $this->stockMinimoPorVariante[$index][$stock->sucursal_id] = $stock->stock_minimo;
                }
            }

        } else {
            // Insumo sin variantes: precargar stock normal
            $stockSucursales = $insumo->stockSucursales;
            $this->stockInicial = $stockSucursales->pluck('cantidad_actual', 'sucursal_id')->toArray();
            $this->stockMinimoPorSucursal = $stockSucursales->pluck('stock_minimo', 'sucursal_id')->toArray();
        }

        $this->modo_edicion = true;
        $this->modal_abierto = true;
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
        $this->filtroKey = uniqid();
    }



    public function updatedTieneVariantes($value)
    {
        if ($value && empty($this->atributos)) {
            $this->atributos[] = 'Atributo 1';
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

            // ⚠️ Forzar Livewire a detectar el cambio
            $this->valoresAtributos = array_merge($this->valoresAtributos);

            $this->generarCombinaciones();
        }
    }



    public function updatedValoresAtributos()
    {
        // Reindexa los arrays de valores para evitar desincronización de índices
        foreach ($this->valoresAtributos as $clave => $valores) {
            $this->valoresAtributos[$clave] = array_values($valores);
        }

        $this->generarCombinaciones();
    }


    public function updatedAtributos()
    {
        $atributosLimpios = array_filter($this->atributos, fn($a) => trim($a) !== '');

        $valoresActualizados = [];

        foreach ($atributosLimpios as $atributo) {
            $valoresActualizados[$atributo] = $this->valoresAtributos[$atributo] ?? [];
        }

        $this->atributos = array_values($atributosLimpios); // reindexar para el frontend
        $this->valoresAtributos = $valoresActualizados;

        // Si ya no hay atributos, forzar reinicio de combinaciones
        if (empty($this->atributos)) {
            $this->combinaciones = [];
        } else {
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


    public function eliminarAtributoPorIndice($index)
    {
        if (!isset($this->atributos[$index])) return;

        $nombre = $this->atributos[$index];

        unset($this->atributos[$index]);
        unset($this->valoresAtributos[$nombre]);

        $this->atributos = array_values($this->atributos);

        $this->generarCombinaciones();
    }

    public function getUnidadesExistentesProperty()
    {
        return Insumo::select('unidad_medida')
            ->distinct()
            ->whereNotNull('unidad_medida')
            ->where('unidad_medida', '!=', '')
            ->orderBy('unidad_medida')
            ->pluck('unidad_medida');
    }


    public function toggleVariantes($insumoId)
    {
        if (in_array($insumoId, $this->filasConVariantesAbiertas)) {
            $this->filasConVariantesAbiertas = array_diff($this->filasConVariantesAbiertas, [$insumoId]);
        } else {
            $this->filasConVariantesAbiertas[] = $insumoId;
        }
    }


    public function mount()
    {
        $this->filtroKey = uniqid(); // para reiniciar visual de filtros
    }

    public function filtrar()
    {
        // solo fuerza render
    }






}
