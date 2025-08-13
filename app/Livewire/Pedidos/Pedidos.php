<?php

namespace App\Livewire\Pedidos;

use App\Models\Pedido;
use Livewire\Component;
use App\Models\HistorialPedido;
use Illuminate\Support\Facades\Auth;

class Pedidos extends Component
{
    public $filtro_folio = '';
    public $filtro_cliente = '';
    public $filtro_fecha = '';
    public $filtro_estado = '';
    public $filtroKey;
    public $mostrar_modal_motivo = false;
    public $motivo = '';
    public $variante_id_motivo;
    public $nuevo_estado = '';

    // 👂 Se refresca cuando el Dashboard avisa que cambió la sucursal
    protected $listeners = [
        'sucursalActualizada' => '$refresh',
    ];

    // Cambia aquí si quieres filtrar por entrega/elaboración, etc.
    // Opciones típicas: sucursal_registro_id | sucursal_entrega_id | sucursal_elaboracion_id
    protected string $campoSucursalFiltro = 'sucursal_registro_id';

    public function mount()
    {
        $this->filtroKey = uniqid();
    }

    /** Sucursal activa tomada de sesión o del usuario */
    public function getSucursalActivaProperty(): int
    {
        return (int) (session('sucursal_activa_id') ?? Auth::user()->sucursal_id);
    }

    public function render()
    {
        $sid = $this->sucursalActiva;

        $pedidos = Pedido::with([
            'cliente',
            'usuario',
            'sucursalEntrega',
            'variantes' => fn ($q) => $q->orderBy('id'),
        ])
            // ✅ filtro por sucursal activa
            ->where($this->campoSucursalFiltro, $sid)

            ->when($this->filtro_folio, fn ($q) =>
            $q->where('id', $this->filtro_folio)
            )
            ->when($this->filtro_cliente, fn ($q) =>
            $q->whereHas('cliente', fn ($c) =>
            $c->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($this->filtro_cliente) . '%'])
            )
            )
            ->when($this->filtro_fecha, fn ($q) =>
            $q->whereDate('fecha_entrega', $this->filtro_fecha)
            )
            ->when($this->filtro_estado, fn ($q) =>
            $q->whereHas('variantes', fn ($v) =>
            $v->where('estado', $this->filtro_estado)
            )
            )
            ->orderByDesc('id')
            ->get();

        return view('livewire.pedidos.pedidos', [
            'pedidos' => $pedidos,
        ])->layout('layouts.app');
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtro_folio',
            'filtro_cliente',
            'filtro_fecha',
            'filtro_estado',
        ]);

        $this->filtroKey = uniqid(); // para forzar reinicio visual
    }

    public function filtrar()
    {
        // solo re-render
    }

    public function actualizarEstado($varianteId, $nuevoEstado)
    {
        if (in_array($nuevoEstado, ['cancelado', 'devuelto'])) {
            $this->mostrar_modal_motivo = true;
            $this->variante_id_motivo = $varianteId;
            $this->nuevo_estado = $nuevoEstado;
            return;
        }

        $this->cambiarEstado($varianteId, $nuevoEstado);
    }

    public function guardarMotivo()
    {
        $this->validate(['motivo' => 'required|string|min:1']);

        $this->cambiarEstado($this->variante_id_motivo, $this->nuevo_estado, $this->motivo);

        $this->reset([
            'motivo',
            'variante_id_motivo',
            'mostrar_modal_motivo',
            'nuevo_estado',
        ]);

        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { tipo: 'success', mensaje: 'Estado actualizado correctamente con motivo' }
            }));
        JS);
    }

    private function cambiarEstado($varianteId, $nuevoEstado, $motivo = null)
    {
        $variante = \App\Models\PedidoServicioVariante::find($varianteId);
        if (!$variante) {
            $this->js(<<<JS
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { tipo: 'error', mensaje: 'No se encontró la variante del servicio' }
                }));
            JS);
            return;
        }

        $variante->estado = $nuevoEstado;
        $variante->save();

        HistorialPedido::create([
            'pedido_servicio_variante_id' => $variante->id,
            'user_id' => Auth::id(),
            'nuevo_estado' => $nuevoEstado,
            'motivo' => $motivo,
        ]);

        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { tipo: 'info', mensaje: 'Estado cambiado a "$nuevoEstado"' }
            }));
        JS);

        $this->dispatch('$refresh');
    }
}
