<?php

namespace App\Livewire\Configuracion;

use App\Models\User;
use App\Models\Rol;
use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Empleados extends Component
{
    public $nombre, $usuario, $password, $rol_id, $sucursal_id, $salario, $estado;
    public $modo_edicion = false, $empleado_id;
    public $search = '';
    public $filtro_nombre = '';
    public $filtro_rol = '';
    public $filtro_estado = '';
    public $filtro_sucursal = '';
    public $filtroKey;

    public $modal_abierto = false;
    public $modalKey;

    protected $listeners = ['cerrarModal', 'abrirModalExterno' => 'abrirModal'];


    public function mount()
    {
        $this->reset();
        $this->limpiarFormulario();
        $this->modal_abierto = false;
        $this->filtroKey = uniqid(); // Nueva clave para reiniciar visualmente filtros
        session()->forget('_livewire'); // fuerza reinicio de estado Livewire
    }

    public function render()
    {
        $empleados = User::with(['rol', 'sucursal'])
            ->when($this->filtro_nombre, fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($this->filtro_nombre) . '%']))
            ->when($this->filtro_rol, fn($q) => $q->where('rol_id', $this->filtro_rol))
            ->when($this->filtro_estado !== '', fn($q) => $q->where('estado', $this->filtro_estado))
            ->when($this->filtro_sucursal, fn($q) => $q->where('sucursal_id', $this->filtro_sucursal))
            ->get();

        return view('livewire.configuracion.empleados', [
            'empleados' => $empleados,
            'roles' => Rol::all(),
            'sucursales' => Sucursal::all(),
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
        $this->nombre = '';
        $this->usuario = '';
        $this->password = '';
        $this->rol_id = '';
        $this->sucursal_id = '';
        $this->salario = '';
        $this->estado = '1';
        $this->empleado_id = null;
        $this->modo_edicion = false;
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => ['required', 'string'],
            'usuario' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->empleado_id),
            ],
            'rol_id' => ['required', 'exists:roles,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'salario' => ['required', 'numeric'],
            'password' => $this->modo_edicion ? ['nullable', 'min:6'] : ['required', 'min:6'],
        ]);

        try {
            if ($this->modo_edicion && $this->empleado_id) {
                $empleado = User::findOrFail($this->empleado_id);

                $empleado->update([
                    'name' => $this->nombre,
                    'email' => $this->usuario,
                    'rol_id' => $this->rol_id,
                    'sucursal_id' => $this->sucursal_id,
                    'estado' => $this->estado === '1' || $this->estado === 1,
                    'salario' => $this->salario,
                    'password' => $this->password
                        ? Hash::make($this->password)
                        : $empleado->password,
                ]);
            } else {
                User::create([
                    'name' => $this->nombre,
                    'email' => $this->usuario,
                    'rol_id' => $this->rol_id,
                    'sucursal_id' => $this->sucursal_id,
                    'estado' => $this->estado === '1' || $this->estado === 1,
                    'salario' => $this->salario,
                    'password' => Hash::make($this->password),
                ]);
            }

            $this->js(<<<'JS'
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        tipo: 'success',
                        mensaje: "Empleado guardado correctamente"
                    }
                }));
            JS);

            $this->cerrarModal();
        } catch (\Exception $e) {
            \Log::error('Error al guardar empleado: ' . $e->getMessage());

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
        $empleado = User::findOrFail($id);

        $this->empleado_id = $empleado->id;
        $this->nombre = $empleado->name;
        $this->usuario = $empleado->email;
        $this->rol_id = $empleado->rol_id;
        $this->sucursal_id = $empleado->sucursal_id;
        $this->estado = $empleado->estado ? '1' : '0';
        $this->salario = $empleado->salario;
        $this->modo_edicion = true;

        $this->modalKey = uniqid();
        $this->modal_abierto = true;
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtro_nombre', 'filtro_rol', 'filtro_estado', 'filtro_sucursal']);
        $this->filtroKey = uniqid(); // fuerza reinicio visual de filtros
    }

    public function filtrar()
    {
        // Método vacío para disparar render si se desea con botón
    }
}
