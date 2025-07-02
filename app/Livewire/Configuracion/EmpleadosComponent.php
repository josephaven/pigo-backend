<?php

namespace App\Livewire\Configuracion;

use App\Models\Empleado;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class EmpleadosComponent extends Component
{
    public $modalOpen = false;
    public $modoEdicion = false;

    public $empleado_id;
    public $nombre, $email, $password, $rol, $sucursal, $salario, $estado;

    public $empleados;

    // Filtros
    public $filtroNombre = '';
    public $filtroRol = '';
    public $filtroSucursal = '';
    public $filtroEstado = '';

    public function mount()
    {
        $this->refrescarEmpleados();
    }

    public function render()
    {
        $this->empleados = Empleado::query()
            ->when($this->filtroNombre, fn($q) =>
                $q->where('nombre', 'like', '%' . $this->filtroNombre . '%'))
            ->when($this->filtroRol, fn($q) =>
                $q->where('rol', $this->filtroRol))
            ->when($this->filtroSucursal, fn($q) =>
                $q->where('sucursal', $this->filtroSucursal))
            ->when($this->filtroEstado !== '', fn($q) =>
                $q->where('activo', $this->filtroEstado))
            ->get();

        return view('livewire.configuracion.empleados-component');
    }

    public function guardarEmpleado()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:empleados,email,' . $this->empleado_id,
            'rol' => 'required',
            'sucursal' => 'required',
            'salario' => 'required|numeric',
        ]);

        if ($this->modoEdicion) {
            $empleado = Empleado::findOrFail($this->empleado_id);
            $empleado->update([
                'nombre' => $this->nombre,
                'email' => $this->email,
                'rol' => $this->rol,
                'sucursal' => $this->sucursal,
                'salario' => $this->salario,
                'activo' => $this->estado,
            ]);
            session()->flash('message', 'Empleado actualizado exitosamente.');
        } else {
            $this->validate([
                'password' => 'required|min:6',
            ]);

            Empleado::create([
                'nombre' => $this->nombre,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'rol' => $this->rol,
                'sucursal' => $this->sucursal,
                'salario' => $this->salario,
                'activo' => true,
            ]);
            session()->flash('message', 'Empleado registrado exitosamente.');
        }

        $this->resetFormulario();
        $this->refrescarEmpleados();
    }

    public function editarEmpleado($id)
    {
        $empleado = Empleado::findOrFail($id);

        $this->empleado_id = $empleado->id;
        $this->nombre = $empleado->nombre;
        $this->email = $empleado->email;
        $this->rol = $empleado->rol;
        $this->sucursal = $empleado->sucursal;
        $this->salario = $empleado->salario;
        $this->estado = $empleado->activo;

        $this->modoEdicion = true;
        $this->modalOpen = true;
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtroNombre', 'filtroRol', 'filtroSucursal', 'filtroEstado']);
    }

    private function resetFormulario()
    {
        $this->reset([
            'modalOpen',
            'modoEdicion',
            'empleado_id',
            'nombre',
            'email',
            'password',
            'rol',
            'sucursal',
            'salario',
            'estado',
        ]);
    }

    private function refrescarEmpleados()
    {
        $this->empleados = Empleado::all();
    }
}
