<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Ruta general
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Ruta protegida solo para Jefe
    Route::get('/admin', function () {
        return 'Bienvenido Jefe';
    })->middleware('rol:Jefe');
});

