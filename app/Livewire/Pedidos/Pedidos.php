<?php

namespace App\Livewire\Pedidos;

use App\Models\Pedido;
use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Pedidos extends Component
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
        $pedidos = Pedido::with(['cliente', 'usuario', 'sucursalEntrega'])
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
            'filtro_estado'
        ]);

        $this->filtroKey = uniqid(); // para forzar reinicio visual
    }

    public function filtrar()
    {
        // No es necesario código. Solo fuerza render.
    }
}

