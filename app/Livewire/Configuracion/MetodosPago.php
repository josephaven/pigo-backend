<?php


namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\MetodoPago;

class MetodosPago extends Component
{
    public $nombre, $descripcion, $tipo, $banco, $cuenta, $clabe, $titular;
    public $metodo_id = null;
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
        return view('livewire.configuracion.metodos-pago', [
            'metodos' => MetodoPago::all()
        ])->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->limpiarFormulario();
        $this->modalKey = uniqid();
        $this->modal_abierto = true;
        $this->resetErrorBag();
    }

    public function cerrarModal()
    {
        $this->modal_abierto = false;
        $this->limpiarFormulario();
        $this->resetErrorBag();
    }

    public function limpiarFormulario()
    {
        $this->reset([
            'metodo_id', 'modo_edicion',
            'nombre', 'descripcion', 'tipo', 'banco',
            'cuenta', 'clabe', 'titular'
        ]);
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:Efectivo,Transferencia,Terminal',
            'descripcion' => 'nullable|string',
            'banco' => 'nullable|string|max:100',
            'cuenta' => 'nullable|string|max:100',
            'clabe' => 'nullable|string|max:100',
            'titular' => 'nullable|string|max:255',
        ]);

        try {
            $esEdicion = $this->metodo_id !== null;

            MetodoPago::updateOrCreate(
                ['id' => $this->metodo_id],
                [
                    'nombre' => $this->nombre,
                    'tipo' => $this->tipo,
                    'descripcion' => $this->descripcion,
                    'banco' => $this->banco,
                    'cuenta' => $this->cuenta,
                    'clabe' => $this->clabe,
                    'titular' => $this->titular
                ]
            );

            $tipoToast = $esEdicion ? 'info' : 'success';
            $mensajeToast = $esEdicion
                ? 'Método de pago actualizado correctamente'
                : 'Método de pago creado correctamente';

            $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    tipo: "$tipoToast",
                    mensaje: "$mensajeToast"
                }
            }));
        JS);

            $this->cerrarModal();

        } catch (\Exception $e) {
            \Log::error('Error al guardar método de pago: ' . $e->getMessage());

            $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    tipo: "error",
                    mensaje: "Ocurrió un error al guardar. Intenta nuevamente."
                }
            }));
        JS);
        }
    }


    public function editar($id)
    {
        $metodo = MetodoPago::findOrFail($id);
        $this->modo_edicion = true;
        $this->metodo_id = $id;

        $this->nombre = $metodo->nombre;
        $this->tipo = $metodo->tipo;
        $this->descripcion = $metodo->descripcion;
        $this->banco = $metodo->banco;
        $this->cuenta = $metodo->cuenta;
        $this->clabe = $metodo->clabe;
        $this->titular = $metodo->titular;

        $this->modalKey = uniqid();
        $this->modal_abierto = true;
    }
}
