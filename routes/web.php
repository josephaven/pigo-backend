<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Configuracion\EmpleadosComponent;

Route::get('/', function () {
    return view('welcome');
});

// Grupo de rutas protegidas
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard general
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Ruta solo para Jefe (ejemplo de texto plano)
    Route::get('/admin', function () {
        return 'Bienvenido Jefe';
    })->middleware('rol:Jefe');

    // Módulo configuración > empleados (requiere Jefe)
    Route::get('/configuracion/empleados', EmpleadosComponent::class)
        ->middleware('rol:Jefe')
        ->name('config.empleados');
});
