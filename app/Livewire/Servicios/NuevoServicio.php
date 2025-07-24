<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\Insumo;
use Livewire\Component;
use Illuminate\Support\Facades\Request;

class NuevoServicio extends Component
{
    public $modo_edicion = false;
    public $servicio_id;

    public $nombre, $tipo_cobro = 'pieza';
    public $precio_normal, $precio_maquilador;
    public $precio_minimo, $usar_cobro_minimo = false;
    public $activo = true;
    public $sucursales_disponibles = [];
    public $sucursales_seleccionadas = [];
    public $sucursal_a_agregar;

    public $insumos_disponibles = [];
    public $insumo_id = null;
    public $cantidad_insumo = null;
    public $unidad_insumo = null;

    // Lista de insumos agregados al servicio (para mostrar en tabla y guardar)
    public $insumos_agregados = []; // cada item será ['id' => ..., 'nombre' => ..., 'categoria' => ..., 'cantidad' => ..., 'unidad' => ...]



    protected $rules = [
        'nombre' => 'required|string|max:100',
        'tipo_cobro' => 'required|in:pieza,m2,ml,otro',
        'precio_normal' => 'required|numeric|min:0',
        'precio_maquilador' => 'required|numeric|min:0',
        'precio_minimo' => 'nullable|numeric|min:0',
        'usar_cobro_minimo' => 'boolean',
        'sucursales_seleccionadas' => 'required|array|min:1',

    ];

    public function mount($servicio = null)
    {
        // Cargar sucursales
        $this->sucursales_disponibles = \App\Models\Sucursal::orderBy('nombre')->get();
        $this->sucursales_seleccionadas = [];
        $this->sucursal_a_agregar = null;

        // Cargar insumos disponibles con su categoría
        $this->insumos_disponibles = \App\Models\Insumo::with('categoria')->orderBy('nombre')->get();
        $this->insumos_agregados = [];

        if ($servicio) {
            $this->modo_edicion = true;
            $registro = \App\Models\Servicio::findOrFail($servicio);
            $this->servicio_id = $registro->id;

            $this->fill($registro->only([
                'nombre', 'tipo_cobro', 'precio_normal',
                'precio_maquilador', 'precio_minimo',
                'usar_cobro_minimo', 'activo'
            ]));

            // Precargar sucursales asociadas
            $this->sucursales_seleccionadas = $registro->sucursales()
                ->pluck('sucursales.id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            // Precargar insumos asociados
            $this->insumos_agregados = $registro->insumos->map(function ($insumo) {
                return [
                    'id' => $insumo->id,
                    'nombre' => $insumo->nombre,
                    'categoria' => $insumo->categoria->nombre ?? '',
                    'cantidad' => $insumo->pivot->cantidad,
                    'unidad' => $insumo->pivot->unidad,
                ];
            })->toArray();
        }
    }




    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|string|max:100',
            'tipo_cobro' => 'required|in:pieza,m2,ml,otro',
            'precio_normal' => 'required|numeric|min:0',
            'precio_maquilador' => 'required|numeric|min:0',
            'precio_minimo' => 'nullable|numeric|min:0',
            'usar_cobro_minimo' => 'boolean',
            'activo' => 'boolean',
            'sucursales_seleccionadas' => 'required|array|min:1',
        ]);

        // ⛔ Validar que al menos un insumo haya sido agregado
        if (count($this->insumos_agregados) === 0) {
            $this->addError('insumos_agregados', 'Debes agregar al menos un insumo.');
            return;
        }

        $servicio = \App\Models\Servicio::updateOrCreate(
            ['id' => $this->servicio_id],
            [
                'nombre' => $this->nombre,
                'tipo_cobro' => $this->tipo_cobro,
                'precio_normal' => $this->precio_normal,
                'precio_maquilador' => $this->precio_maquilador,
                'precio_minimo' => $this->precio_minimo,
                'usar_cobro_minimo' => $this->usar_cobro_minimo,
                'activo' => $this->activo,
            ]
        );

        $servicio->sucursales()->sync($this->sucursales_seleccionadas);

        $servicio->insumos()->sync([]); // limpia antes
        foreach ($this->insumos_agregados as $item) {
            $servicio->insumos()->attach($item['id'], [
                'cantidad' => $item['cantidad'],
                'unidad' => $item['unidad'],
            ]);
        }

        return redirect()->route('servicios')->with('mensaje', $this->modo_edicion ? 'Servicio actualizado' : 'Servicio creado');
    }



    public function render()
    {
        return view('livewire.servicios.nuevo-servicio')->layout('layouts.app');
    }

    public function agregarSucursal()
    {
        if ($this->sucursal_a_agregar !== null) {
            $id = (int) $this->sucursal_a_agregar;

            if (!in_array($id, array_map('intval', $this->sucursales_seleccionadas), true)) {
                $this->sucursales_seleccionadas[] = $id;
            }

            $this->sucursal_a_agregar = null;
        }
    }



    public function quitarSucursal($id)
    {
        $this->sucursales_seleccionadas = array_values(array_filter(
            $this->sucursales_seleccionadas,
            fn($sucursalId) => $sucursalId != $id
        ));
    }

    public function agregarInsumo()
    {
        $this->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad_insumo' => 'required|numeric|min:0.01',
            'unidad_insumo' => 'required|string|max:50',
        ]);

        // Verifica que no esté ya agregado
        foreach ($this->insumos_agregados as $item) {
            if ($item['id'] == $this->insumo_id) {
                return; // ya está agregado
            }
        }

        // Buscar el insumo en la colección cargada
        $insumo = $this->insumos_disponibles->firstWhere('id', $this->insumo_id);

        if ($insumo) {
            $this->insumos_agregados[] = [
                'id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'categoria' => $insumo->categoria->nombre ?? '',
                'cantidad' => $this->cantidad_insumo,
                'unidad' => $this->unidad_insumo,
            ];

            // Limpiar campos
            $this->insumo_id = null;
            $this->cantidad_insumo = null;
            $this->unidad_insumo = null;
        }
    }

    public function quitarInsumo($id)
    {
        $this->insumos_agregados = array_values(array_filter(
            $this->insumos_agregados,
            fn($item) => $item['id'] != $id
        ));
    }

    public function getUnidadesExistentesProperty()
    {
        return \App\Models\Insumo::distinct()
            ->pluck('unidad_medida')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }



}

