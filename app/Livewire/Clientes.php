<?php


namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Clientes extends Component
{
    public $nombre_completo, $telefono, $tipo_cliente = 'Normal', $ocupacion, $fecha_nacimiento;
    public $cliente_id;
    public $modo_edicion = false;
    public $modal_abierto = false;
    public $modalKey;
    public $ocupacionesUnicas = [];

    // Filtros
    public $filtro_nombre = '';
    public $filtro_tipo = '';
    public $filtro_telefono = '';
    public $filtro_ocupacion = '';
    public $filtro_fecha = '';
    public $filtroKey;

    public $filtro_mes_nacimiento = '';
    public $filtro_anio_nacimiento = '';

    protected $listeners = ['cerrarModal', 'abrirModalExterno' => 'abrirModal'];

    public function mount()
    {
        $this->limpiarFormulario();
        $this->modal_abierto = false;
        $this->filtroKey = uniqid();
        session()->forget('_livewire');
    }

    public function render()
    {
        $this->ocupacionesUnicas = Cliente::select('ocupacion')->distinct()->pluck('ocupacion')->filter()->values();

        $clientes = Cliente::with('sucursal')
            ->when($this->filtro_nombre, fn($q) => $q->whereRaw('LOWER(nombre_completo) LIKE ?', ['%' . strtolower($this->filtro_nombre) . '%']))
            ->when($this->filtro_tipo, fn($q) => $q->where('tipo_cliente', $this->filtro_tipo))
            ->when($this->filtro_telefono, fn($q) => $q->where('telefono', 'like', "%{$this->filtro_telefono}%"))
            ->when($this->filtro_ocupacion, fn($q) => $q->where('ocupacion', 'like', "%{$this->filtro_ocupacion}%"))
            ->when($this->filtro_fecha, fn($q) => $q->whereDate('fecha_nacimiento', $this->filtro_fecha))
            ->when($this->filtro_mes_nacimiento, fn($q) => $q->whereMonth('fecha_nacimiento', $this->filtro_mes_nacimiento))
            ->when($this->filtro_anio_nacimiento, fn($q) => $q->whereYear('fecha_nacimiento', $this->filtro_anio_nacimiento))
            ->get();

        return view('livewire.clientes', [
            'clientes' => $clientes,
            'sucursales' => Sucursal::all(),
            'ocupacionesUnicas' => $this->ocupacionesUnicas,
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
        $this->cliente_id = null;
        $this->nombre_completo = '';
        $this->telefono = '';
        $this->tipo_cliente = 'Normal';
        $this->ocupacion = '';
        $this->fecha_nacimiento = '';
        $this->modo_edicion = false;
    }

    public function guardar()
    {
        $this->validate([
            'nombre_completo' => ['required', 'string'],
            'telefono' => ['required', 'string', Rule::unique('clientes', 'telefono')->ignore($this->cliente_id)],
            'tipo_cliente' => ['required', 'in:Normal,Frecuente,Maquilador'],
            'ocupacion' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
        ]);

        $data = [
            'nombre_completo' => $this->nombre_completo,
            'telefono' => $this->telefono,
            'tipo_cliente' => $this->tipo_cliente,
            'ocupacion' => $this->ocupacion,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'sucursal_id' => Auth::user()->sucursal_id,
        ];

        try {
            if ($this->modo_edicion && $this->cliente_id) {
                Cliente::findOrFail($this->cliente_id)->update($data);
            } else {
                Cliente::create($data);
            }

            $this->js(<<<'JS'
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        tipo: 'success',
                        mensaje: "Cliente guardado correctamente"
                    }
                }));
            JS
            );

            $this->cerrarModal();
        } catch (\Exception $e) {
            \Log::error('Error al guardar cliente: ' . $e->getMessage());

            $this->js(<<<'JS'
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        tipo: 'error',
                        mensaje: "Ocurrió un error al guardar. Intenta nuevamente."
                    }
                }));
            JS
            );
        }
    }

    public function editar($id)
    {
        $cliente = Cliente::findOrFail($id);

        $this->cliente_id = $cliente->id;
        $this->nombre_completo = $cliente->nombre_completo;
        $this->telefono = $cliente->telefono;
        $this->tipo_cliente = $cliente->tipo_cliente;
        $this->ocupacion = $cliente->ocupacion;
        $this->fecha_nacimiento = $cliente->fecha_nacimiento;
        $this->modo_edicion = true;

        $this->modalKey = uniqid();
        $this->modal_abierto = true;
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'filtro_nombre',
            'filtro_tipo',
            'filtro_telefono',
            'filtro_ocupacion',
            'filtro_fecha',
            'filtro_mes_nacimiento',
            'filtro_anio_nacimiento'
        ]);
        $this->filtroKey = uniqid(); // fuerza reinicio visual
    }


    public function filtrar()
    {
        // No se requiere código; fuerza render si se llama desde botón
    }
}
