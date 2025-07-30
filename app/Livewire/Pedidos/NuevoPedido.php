<?php

namespace App\Livewire\Pedidos;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NuevoPedido extends Component
{
    public $pedido_id;
    public $cliente_id;
    public $sucursal_registro_id;
    public $sucursal_entrega_id;
    public $sucursal_elaboracion_id;
    public $fecha_entrega;
    public $anticipo = 0;
    public $total = 0;
    public $justificacion_precio = '';

    public $modo_edicion = false;

    // Cliente nuevo
    public $cliente_nuevo = false;
    public $nombre_cliente;
    public $telefono_cliente;
    public $tipo_cliente = 'Normal';
    public $ocupacion_cliente;
    public $fecha_nacimiento;

    // Buscador tipo Google
    public $busqueda_cliente = '';
    public $clientes_sugeridos = [];
    public $mostrar_sugerencias = false;
    public $forzar_render = 0;

    public $cliente_seleccionado;

    public function mount($id = null)
    {
        $this->sucursal_registro_id = Auth::user()->sucursal_id;
        $this->fecha_entrega = now()->addDays(2)->format('Y-m-d');

        if ($id) {
            $this->modo_edicion = true;
            $this->cargarPedido($id);
        }
    }

    public function render()
    {
        return view('livewire.pedidos.nuevo-pedido', [
            'sucursales' => Sucursal::all(),
        ])->layout('layouts.app');
    }


    public function updated($prop)
    {
        \Log::info('Busqueda actual:', ['q' => $this->busqueda_cliente]);
        if ($prop === 'busqueda_cliente') {
            $this->forzar_render++;
            if (strlen($this->busqueda_cliente) < 2) {
                $this->clientes_sugeridos = [];
                $this->mostrar_sugerencias = false;
                return;
            }

            $this->clientes_sugeridos = Cliente::where(function ($query) {
                $query->where('nombre_completo', 'ILIKE', '%' . $this->busqueda_cliente . '%')
                    ->orWhere('telefono', 'ILIKE', '%' . $this->busqueda_cliente . '%');
            })->limit(5)->get();

            $this->mostrar_sugerencias = true;
        }
    }

    public function seleccionarCliente($id)
    {
        $cliente = Cliente::find($id);

        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->busqueda_cliente = $cliente->nombre_completo;
            $this->mostrar_sugerencias = false;
            $this->cliente_seleccionado = $cliente;
        }
    }

    public function actualizarSugerencias()
    {
        if (strlen($this->busqueda_cliente) < 2) {
            $this->clientes_sugeridos = [];
            $this->mostrar_sugerencias = false;
            return;
        }

        $this->clientes_sugeridos = Cliente::where(function ($query) {
            $query->where('nombre_completo', 'ILIKE', '%' . $this->busqueda_cliente . '%')
                ->orWhere('telefono', 'ILIKE', '%' . $this->busqueda_cliente . '%');
        })->limit(5)->get();

        $this->mostrar_sugerencias = true;
    }


    public function updatedClienteNuevo($valor)
    {
        if ($valor) {
            $this->reset([
                'cliente_id',
                'busqueda_cliente',
                'clientes_sugeridos',
                'mostrar_sugerencias',
            ]);
        }
    }

    public function cargarPedido($id)
    {
        $pedido = Pedido::findOrFail($id);

        $this->pedido_id = $pedido->id;
        $this->cliente_id = $pedido->cliente_id;
        $this->sucursal_entrega_id = $pedido->sucursal_entrega_id;
        $this->sucursal_elaboracion_id = $pedido->sucursal_elaboracion_id;
        $this->fecha_entrega = $pedido->fecha_entrega;
        $this->anticipo = $pedido->anticipo;
        $this->total = $pedido->total;
        $this->justificacion_precio = $pedido->justificacion_precio;
    }

    public function guardar()
    {
        // Registro de cliente nuevo
        if ($this->cliente_nuevo) {
            $this->validate([
                'nombre_cliente' => 'required|string|max:255',
                'telefono_cliente' => ['required', Rule::unique('clientes', 'telefono')],
                'tipo_cliente' => ['required', 'in:Normal,Frecuente,Maquilador'],
                'ocupacion_cliente' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date|before:today',
            ]);

            $nuevo = Cliente::create([
                'nombre_completo' => $this->nombre_cliente,
                'telefono' => $this->telefono_cliente,
                'tipo_cliente' => $this->tipo_cliente,
                'ocupacion' => $this->ocupacion_cliente,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sucursal_id' => $this->sucursal_registro_id,
            ]);

            $this->cliente_id = $nuevo->id;
            $this->cliente_nuevo = false;
            $this->dispatch('toast', 'Cliente nuevo registrado correctamente');
        }

        // Validación final del pedido
        $this->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')],
            'sucursal_entrega_id' => ['required', 'different:sucursal_elaboracion_id'],
            'sucursal_elaboracion_id' => ['required'],
            'fecha_entrega' => ['required', 'date', 'after_or_equal:today'],
            'anticipo' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
        ]);

        $data = [
            'cliente_id' => $this->cliente_id,
            'sucursal_registro_id' => $this->sucursal_registro_id,
            'sucursal_entrega_id' => $this->sucursal_entrega_id,
            'sucursal_elaboracion_id' => $this->sucursal_elaboracion_id,
            'fecha_entrega' => $this->fecha_entrega,
            'anticipo' => $this->anticipo,
            'total' => $this->total,
            'justificacion_precio' => $this->justificacion_precio,
            'user_id' => Auth::id(),
        ];

        if ($this->modo_edicion && $this->pedido_id) {
            Pedido::findOrFail($this->pedido_id)->update($data);
            session()->flash('mensaje', 'Pedido actualizado correctamente');
        } else {
            $pedido = Pedido::create($data);
            $this->pedido_id = $pedido->id;
            session()->flash('mensaje', 'Pedido creado correctamente');
        }

        return redirect()->route('pedidos');
    }
}
