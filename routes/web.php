<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Configuracion\Empleados;
use App\Livewire\Configuracion\Sucursales;
use App\Livewire\Configuracion\MetodosPago;
use App\Livewire\Clientes;
use App\Livewire\Inventario\Insumos;
use App\Livewire\Inventario\Traslados;
use App\Livewire\Inventario\Mermas;
use App\Livewire\Inventario\Categorias;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

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



    // Aquí puedes agregar otras rutas por módulo en el futuro...
});
