<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Servicios extends Component
{
    public $servicios;
    public $filtro_nombre = '';
    public $filtro_estado = '';
    public $filtro_cobro = '';
    public $filtroKey;

    // Escucha el cambio de sucursal que dispara el Dashboard
    protected $listeners = [
        'sucursalActualizada' => 'onSucursalActualizada',
    ];

    public function mount()
    {
        $this->filtroKey = uniqid(); // Para reiniciar campos
    }

    public function render()
    {
        // Sucursal activa (si no hay en sesión, usa la del usuario)
        $sucursalActivaId = session('sucursal_activa_id', Auth::user()->sucursal_id);

        $this->servicios = Servicio::with('sucursales')
            // Visibilidad por sucursal:
            // - Sin sucursales asociadas => visible en todas
            // - Con sucursales asociadas => visible solo en esas
            ->where(function ($q) use ($sucursalActivaId) {
                $q->doesntHave('sucursales')
                    ->orWhereHas('sucursales', function ($s) use ($sucursalActivaId) {
                        $s->where('sucursales.id', $sucursalActivaId);
                    });
            })
            ->when($this->filtro_nombre, fn ($q) =>
            $q->where('nombre', 'ilike', '%' . $this->filtro_nombre . '%')
            )
            ->when($this->filtro_estado !== '', fn ($q) =>
            $q->where('activo', $this->filtro_estado)
            )
            ->when($this->filtro_cobro, fn ($q) =>
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

    public function onSucursalActualizada(): void
    {
        // Si quieres, limpia filtros al cambiar de sucursal:
        // $this->limpiarFiltros();

        // Con que se re-renderice basta porque el query usa la sesión
        $this->dispatch('$refresh');
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
        // Solo refresca el render.
    }
}
