<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\MetodoPago;

class MetodosPago extends Component
{
    public $nombre, $descripcion, $metodo_id;
    public $modal_abierto = false;
    public $modo_edicion = false;
    public $search = '';

    public function render()
    {
        $metodos = MetodoPago::where('nombre', 'like', "%{$this->search}%")->get();

        return view('livewire.configuracion.metodos-pago', [
            'metodos' => $metodos
        ])->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->reset(['nombre', 'descripcion', 'modo_edicion', 'metodo_id']);
        $this->modal_abierto = true;
    }

    public function cerrarModal()
    {
        $this->modal_abierto = false;
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        MetodoPago::updateOrCreate(
            ['id' => $this->metodo_id],
            ['nombre' => $this->nombre, 'descripcion' => $this->descripcion]
        );

        $this->cerrarModal();
    }

    public function editar($id)
    {
        $metodo = MetodoPago::findOrFail($id);
        $this->metodo_id = $id;
        $this->nombre = $metodo->nombre;
        $this->descripcion = $metodo->descripcion;
        $this->modo_edicion = true;
        $this->modal_abierto = true;
    }
}

