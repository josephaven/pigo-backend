<?php

namespace App\Livewire\Configuracion;

use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Sucursales extends Component
{
    public $nombre, $calle_numero, $colonia, $municipio, $estado, $telefono, $fecha_apertura;
    public $sucursal_id = null;
    public $modo_edicion = false;
    public $modal_abierto = false;
    public $modalKey;

    protected $listeners = ['cerrarModal', 'abrirModalExterno' => 'abrirModal'];

    public function mount()
    {
        $this->modal_abierto = false;
        $this->limpiarFormulario();
    }

    public function render()
    {
        $sucursales = Sucursal::withCount('empleados')->get();

        return view('livewire.configuracion.sucursales', [
            'sucursales' => $sucursales,
        ])->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->limpiarFormulario();
        $this->modalKey = uniqid();
        $this->modal_abierto = true;
    }

    public function cerrarModal()
    {
        $this->modal_abierto = false;
        $this->limpiarFormulario();
    }

    public function limpiarFormulario()
    {
        $this->sucursal_id = null;
        $this->modo_edicion = false;
        $this->nombre = '';
        $this->calle_numero = '';
        $this->colonia = '';
        $this->municipio = '';
        $this->estado = '';
        $this->telefono = '';
        $this->fecha_apertura = '';
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'calle_numero' => 'required|string|max:255',
            'colonia' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha_apertura' => 'required|date',
        ]);

        try {
            if ($this->modo_edicion && $this->sucursal_id) {
                $sucursal = Sucursal::findOrFail($this->sucursal_id);
                $sucursal->update([
                    'nombre' => $this->nombre,
                    'calle_numero' => $this->calle_numero,
                    'colonia' => $this->colonia,
                    'municipio' => $this->municipio,
                    'estado' => $this->estado,
                    'telefono' => $this->telefono,
                    'fecha_apertura' => $this->fecha_apertura,
                ]);
            } else {
                Sucursal::create([
                    'nombre' => $this->nombre,
                    'calle_numero' => $this->calle_numero,
                    'colonia' => $this->colonia,
                    'municipio' => $this->municipio,
                    'estado' => $this->estado,
                    'telefono' => $this->telefono,
                    'fecha_apertura' => $this->fecha_apertura,
                ]);
            }

            $this->js(<<<'JS'
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        tipo: 'success',
                        mensaje: "Sucursal guardada correctamente"
                    }
                }));
            JS);

            $this->cerrarModal();
        } catch (\Exception $e) {
            \Log::error('Error al guardar sucursal: ' . $e->getMessage());

            $this->js(<<<'JS'
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        tipo: 'error',
                        mensaje: "Ocurrió un error al guardar. Intenta nuevamente."
                    }
                }));
            JS);
        }
    }

    public function editar($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $this->sucursal_id = $sucursal->id;
        $this->modo_edicion = true;

        $this->nombre = $sucursal->nombre;
        $this->calle_numero = $sucursal->calle_numero;
        $this->colonia = $sucursal->colonia;
        $this->municipio = $sucursal->municipio;
        $this->estado = $sucursal->estado;
        $this->telefono = $sucursal->telefono;
        $this->fecha_apertura = $sucursal->fecha_apertura;

        $this->modalKey = uniqid();
        $this->modal_abierto = true;
    }
}
