<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $sucursal_id;
    public $sucursales = [];
    public $esJefe = false;

    public function mount()
    {
        $this->esJefe = Auth::user()->rol->nombre === 'Jefe';

        if (!session()->has('sucursal_activa_id')) {
            session(['sucursal_activa_id' => Auth::user()->sucursal_id]);
        }

        $this->sucursal_id = session('sucursal_activa_id');

        // ✅ Cargar sucursales solo si es jefe
        if ($this->esJefe) {
            $this->sucursales = Sucursal::orderBy('nombre')->get();
        }
    }



    public function updatedSucursalId()
    {
        session(['sucursal_activa_id' => $this->sucursal_id]);

        // ⚠️ Aquí NO redirigimos ni recargamos desde Livewire
        // Solo lanzamos un evento JS para redirigir correctamente
        $this->dispatchBrowserEvent('sucursal-cambiada');
    }




    public function render()
    {
        $sucursalId = sucursal_activa_id();
        $sucursalNombre = Sucursal::find($sucursalId)?->nombre ?? 'Sin sucursal';

        return view('livewire.dashboard', [
            'sucursales' => $this->sucursales,
            'esJefe' => $this->esJefe,
            'sucursalNombre' => $sucursalNombre,
        ])->layout('layouts.app');
    }
}
