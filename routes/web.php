<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Configuracion\Empleados;
use App\Livewire\Configuracion\Sucursales;
use App\Livewire\Configuracion\MetodosPago;

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

    // ✅ EN LIVEWIRE v3 USAS DIRECTAMENTE LA CLASE
    Route::get('/configuracion', Empleados::class)->name('configuracion.index');


});
