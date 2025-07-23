<?php

use App\Http\Controllers\SucursalActivaController;
use App\Livewire\Clientes;
use App\Livewire\Configuracion\Empleados;
use App\Livewire\Configuracion\MetodosPago;
use App\Livewire\Configuracion\Sucursales;
use App\Livewire\Dashboard;
use App\Livewire\Inventario\Categorias;
use App\Livewire\Inventario\Insumos;
use App\Livewire\Inventario\Mermas;
use App\Livewire\Inventario\Traslados;
use App\Livewire\Servicios\Servicios;
use Illuminate\Support\Facades\Route;
use App\Livewire\Servicios\NuevoServicio;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::post('/cambiar-sucursal', [SucursalActivaController::class, 'cambiar'])->name('cambiar-sucursal');


    // Acceso restringido por rol
    Route::get('/admin', fn() => 'Bienvenido Jefe')->middleware('rol:Jefe');

    // Módulo Clientes
    Route::get('/clientes', Clientes::class)->name('clientes');

    // Módulo Configuración
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('empleados', Empleados::class)->name('empleados');
        Route::get('sucursales', Sucursales::class)->name('sucursales');
        Route::get('metodos-pago', MetodosPago::class)->name('metodos-pago');
        Route::redirect('', 'configuracion/empleados');
    });

    // Módulo Inventario
    Route::get('/inventario', Insumos::class)->name('inventario.insumos');
    Route::get('/inventario/traslados', Traslados::class)->name('inventario.traslados');
    Route::get('/inventario/mermas', Mermas::class)->name('inventario.mermas');
    Route::get('/inventario/categorias', Categorias::class)->name('inventario.categorias');


    // Módulo Servicios
    Route::get('/servicios', Servicios::class)->name('servicios');
    Route::get('/servicios/nuevo', NuevoServicio::class)->name('servicios.nuevo');
    //Route::get('/servicios/editar/{servicio}', NuevoServicio::class)->name('servicios.editar');
    // Aquí puedes agregar otras rutas por módulo en el futuro...
});
