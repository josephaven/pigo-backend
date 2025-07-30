<?php

namespace App\Livewire\Pedidos;

use App\Models\Pedido;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PedidosParaElaboracion extends Component
{
    public $filtro_folio = '';
    public $filtro_cliente = '';
    public $filtro_fecha = '';
    public $filtro_estado = '';
    public $filtroKey;

    public function mount()
    {
        $this->filtroKey = uniqid();
    }

    public function render()
    {
        $sucursal = Auth::user()->sucursal_id;

        $pedidos = Pedido::with(['cliente', 'variantes'])
            ->where('sucursal_elaboracion_id', $sucursal)
            ->when($this->filtro_folio, fn($q) => $q->where('id', $this->filtro_folio))
            ->when($this->filtro_cliente, fn($q) =>
            $q->whereHas('cliente', fn($c) =>
            $c->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($this->filtro_cliente) . '%'])
            )
            )
            ->when($this->filtro_fecha, fn($q) => $q->whereDate('fecha_entrega', $this->filtro_fecha))
            ->when($this->filtro_estado, fn($q) =>
            $q->whereHas('variantes', fn($v) => $v->where('estado', $this->filtro_estado))
            )
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
            'filtro_estado',
        ]);

        $this->filtroKey = uniqid();
    }

    public function filtrar()
    {
        // fuerza render
    }
}

