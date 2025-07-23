<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
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

    protected $rules = [
        'nombre' => 'required|string|max:100',
        'tipo_cobro' => 'required|in:pieza,m2,ml,otro',
        'precio_normal' => 'required|numeric|min:0',
        'precio_maquilador' => 'required|numeric|min:0',
        'precio_minimo' => 'nullable|numeric|min:0',
        'usar_cobro_minimo' => 'boolean',
    ];

    public function mount($servicio = null)
    {
        if ($servicio) {
            $this->modo_edicion = true;
            $registro = Servicio::findOrFail($servicio);
            $this->servicio_id = $registro->id;
            $this->fill($registro->only([
                'nombre', 'tipo_cobro', 'precio_normal',
                'precio_maquilador', 'precio_minimo',
                'usar_cobro_minimo', 'activo'
            ]));
        }
    }

    public function guardar()
    {
        $this->validate();

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

        return redirect()->route('servicios')->with('mensaje', $this->modo_edicion ? 'Servicio actualizado' : 'Servicio creado');
    }

    public function render()
    {
        return view('livewire.servicios.nuevo-servicio')->layout('layouts.app');
    }
}

