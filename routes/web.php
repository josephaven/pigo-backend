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
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/admin', fn() => 'Bienvenido Jefe')->middleware('rol:Jefe');

    Route::get('/clientes', Clientes::class)->name('clientes');

    // Configuración (igual que antes)
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('empleados', Empleados::class)->name('empleados');
        Route::get('sucursales', Sucursales::class)->name('sucursales');
        Route::get('metodos-pago', MetodosPago::class)->name('metodos-pago');
        Route::redirect('', 'configuracion/empleados');
    });


});
