<?php

namespace App\Livewire\Pedidos;

use App\Models\Pedido;
use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\HistorialPedido;
use Illuminate\Support\Facades\Auth;

class PedidosParaElaboracion extends Component
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

    public function mount()
    {
        $this->filtroKey = uniqid();
    }

    public function render()
    {
        $pedidos = Pedido::with(['cliente', 'usuario', 'sucursalEntrega', 'variantes' => fn($q) => $q->orderBy('id')])
            // 👇 ÚNICA DIFERENCIA: filtrar por sucursal de elaboración = sucursal activa
            ->where('sucursal_elaboracion_id', sucursal_activa_id())
            ->when($this->filtro_folio, fn($q) =>
            $q->where('id', $this->filtro_folio)
            )
            ->when($this->filtro_cliente, fn($q) =>
            $q->whereHas('cliente', fn($c) =>
            $c->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($this->filtro_cliente) . '%'])
            )
            )
            ->when($this->filtro_fecha, fn($q) =>
            $q->whereDate('fecha_entrega', $this->filtro_fecha)
            )
            ->when($this->filtro_estado, function ($q) {
                $q->whereHas('variantes', fn($v) =>
                $v->where('estado', $this->filtro_estado)
                );
            })
            ->orderByDesc('id')
            ->get();

        return view('livewire.pedidos.pedidos-para-elaboracion', [
            'pedidos' => $pedidos,
        ])->layout('layouts.app');
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtro_folio',
            'filtro_cliente',
            'filtro_fecha',
            'filtro_estado'
        ]);

        $this->filtroKey = uniqid(); // para forzar reinicio visual
    }

    public function filtrar()
    {
        // No es necesario código. Solo fuerza render.
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
        $this->validate(['motivo' => 'required|string|min:3']);

        $this->cambiarEstado($this->variante_id_motivo, $this->nuevo_estado, $this->motivo);

        $this->reset([
            'motivo',
            'variante_id_motivo',
            'mostrar_modal_motivo',
            'nuevo_estado'
        ]);

        // ✅ Toast después de guardar motivo
        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    tipo: 'success',
                    mensaje: 'Estado actualizado correctamente con motivo'
                }
            }));
        JS);
    }

    private function cambiarEstado($varianteId, $nuevoEstado, $motivo = null)
    {
        $variante = \App\Models\PedidoServicioVariante::find($varianteId);
        if (!$variante) {
            // ❌ Toast de error
            $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    tipo: 'error',
                    mensaje: 'No se encontró la variante del servicio'
                }
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

        // ✅ Toast de confirmación general
        $this->js(<<<JS
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                tipo: 'info',
                mensaje: 'Estado cambiado a "$nuevoEstado"'
            }
        }));
    JS);

        // 🔁 Forzar refresco visual
        $this->dispatch('$refresh');
    }
}
