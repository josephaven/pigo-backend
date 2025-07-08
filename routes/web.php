<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Configuracion\Empleados;
use App\Livewire\Configuracion\Sucursales;
use App\Livewire\Configuracion\MetodosPago;
use App\Livewire\Clientes;


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin', function () {
        return 'Bienvenido Jefe';
    })->middleware('rol:Jefe');

    Route::get('/clientes', Clientes::class)->name('clientes');


    // Submódulos de configuración
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/empleados', Empleados::class)->name('empleados');
        Route::get('/sucursales', Sucursales::class)->name('sucursales');
        Route::get('/metodos-pago', MetodosPago::class)->name('metodos-pago');

        // También puedes redirigir /configuracion al submódulo de empleados por defecto:
        Route::redirect('/', '/configuracion/empleados');
    });
});

