<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\Insumo;
use App\Models\CampoPersonalizado;
use App\Models\OpcionCampo;
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
    public $campos_personalizados = [];
    public $campo_nombre;
    public $campo_tipo = 'texto'; // opciones: texto, numero, booleano, select
    public $campo_requerido = false;
    public $campo_activo = true;
    public $campo_opciones = ''; // texto separado por comas si es tipo select





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

        // Inicializar campos personalizados
        $this->campos_personalizados = [];
        $this->campo_tipo = 'texto';

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

        if (count($this->insumos_agregados) === 0) {
            $this->addError('insumos_agregados', 'Debes agregar al menos un insumo.');
            return;
        }

        // Guardar o actualizar el servicio
        $servicio = Servicio::updateOrCreate(
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

        // Sincronizar sucursales
        $servicio->sucursales()->sync($this->sucursales_seleccionadas);

        // Sincronizar insumos
        $servicio->insumos()->sync([]);
        foreach ($this->insumos_agregados as $item) {
            $servicio->insumos()->attach($item['id'], [
                'cantidad' => $item['cantidad'],
                'unidad' => $item['unidad'],
            ]);
        }

        // Eliminar campos personalizados anteriores si está editando
        if ($this->modo_edicion) {
            $servicio->camposPersonalizados()->each(function ($campo) {
                $campo->opciones()->delete();
                $campo->delete();
            });
        }

        // Guardar los campos personalizados nuevos
        foreach ($this->campos_personalizados as $i => $campo) {
            $nuevoCampo = CampoPersonalizado::create([
                'servicio_id' => $servicio->id,
                'nombre' => $campo['nombre'],
                'tipo' => $campo['tipo'],
                'requerido' => $campo['requerido'] ?? false,
                'orden' => $i, // puedes ajustar el orden según el índice
            ]);

            // Si es tipo select, guardar las opciones
            if ($campo['tipo'] === 'select' && !empty($campo['opciones'])) {
                foreach ($campo['opciones'] as $opcion) {
                    $nuevoCampo->opciones()->create(['valor' => $opcion]);
                }
            }
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

    public function agregarCampoPersonalizado()
    {
        $this->validate([
            'campo_nombre' => 'required|string|max:100',
            'campo_tipo' => 'required|in:texto,numero,booleano,select',
            'campo_requerido' => 'boolean',
            'campo_activo' => 'boolean',
            'campo_opciones' => 'nullable|string',
        ]);

        // Validar que no haya otro campo con el mismo nombre
        $nombre = strtolower(trim($this->campo_nombre));
        foreach ($this->campos_personalizados as $campo) {
            if (strtolower($campo['nombre']) === $nombre) {
                $this->addError('campo_nombre', 'Ya existe un campo con este nombre.');
                return;
            }
        }

        // Validar opciones en tipo select
        $opciones = [];
        if ($this->campo_tipo === 'select') {
            $opciones = array_filter(array_map('trim', explode(',', $this->campo_opciones)));

            if (empty($opciones)) {
                $this->addError('campo_opciones', 'Debes ingresar al menos una opción válida.');
                return;
            }
        }

        $this->campos_personalizados[] = [
            'nombre' => $this->campo_nombre,
            'tipo' => $this->campo_tipo,
            'requerido' => $this->campo_requerido,
            'activo' => $this->campo_activo,
            'opciones' => $opciones,
        ];

        // Ordenar alfabéticamente por nombre (opcional)
        usort($this->campos_personalizados, function ($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });

        // Limpiar campos del formulario
        $this->reset([
            'campo_nombre',
            'campo_tipo',
            'campo_requerido',
            'campo_activo',
            'campo_opciones',
        ]);

        // Restaurar valores por defecto (para evitar nulls)
        $this->campo_tipo = 'texto';
        $this->campo_requerido = false;
        $this->campo_activo = true;
        $this->campo_opciones = '';
    }



    public function quitarCampoPersonalizado($index)
    {
        unset($this->campos_personalizados[$index]);
        $this->campos_personalizados = array_values($this->campos_personalizados);
    }




}

