<?php

namespace App\Livewire\Inventario;

use App\Models\CategoriaInsumo;
use Livewire\Component;

class Categorias extends Component
{
    public $categorias;

    public $nombre, $descripcion;
    public $categoria_id;
    public $modo_edicion = false;
    public $modal_abierto = false;
    protected $listeners = [
        'abrirModal' => 'abrirModal',
    ];


    protected $rules = [
        'nombre' => 'required|string|max:100',
        'descripcion' => 'nullable|string|max:255',
    ];

    public function render()
    {
        $this->categorias = CategoriaInsumo::orderBy('nombre')->get();
        return view('livewire.inventario.categorias')->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->reset(['nombre', 'descripcion', 'categoria_id', 'modo_edicion']);
        $this->modal_abierto = true;
    }

    public function cerrarModal()
    {
        $this->reset(['modal_abierto', 'nombre', 'descripcion', 'categoria_id', 'modo_edicion']);
    }

    public function guardar()
    {
        $this->validate();

        if ($this->modo_edicion && $this->categoria_id) {
            CategoriaInsumo::findOrFail($this->categoria_id)->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
        } else {
            CategoriaInsumo::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
        }

        $this->cerrarModal();
    }

    public function editar($id)
    {
        $categoria = CategoriaInsumo::findOrFail($id);
        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        $this->modo_edicion = true;
        $this->modal_abierto = true;
    }
}
