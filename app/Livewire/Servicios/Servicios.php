<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Livewire\Component;

class Servicios extends Component
{
    public $servicios;
    public $filtro_nombre = '';
    public $filtro_estado = '';
    public $filtro_cobro = '';
    public $filtroKey;



    public function mount()
    {
        $this->filtroKey = uniqid(); // Para reiniciar campos
    }

    public function render()
    {
        $this->servicios = Servicio::with('sucursales')
            ->when($this->filtro_nombre, fn($q) =>
            $q->where('nombre', 'ilike', '%' . $this->filtro_nombre . '%')
            )
            ->when($this->filtro_estado !== '', fn($q) =>
            $q->where('activo', $this->filtro_estado)
            )
            ->when($this->filtro_cobro, fn($q) =>
            $q->where('tipo_cobro', $this->filtro_cobro)
            )
            ->orderBy('nombre')
            ->get();

        return view('livewire.servicios.servicios')->layout('layouts.app');
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtro_nombre',
            'filtro_estado',
            'filtro_cobro',
        ]);

        $this->filtroKey = uniqid(); // fuerza reinicio visual
    }


    public function eliminar($id)
    {
        Servicio::findOrFail($id)->delete();
    }

    public function redirigirEditar($id)
    {
        return redirect()->route('servicios.editar', $id);
    }

    public function filtrar()
    {
        // Este método no necesita lógica, se usa solo para refrescar los datos
    }

}
