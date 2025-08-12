<?php

use App\Http\Controllers\DescargaComprobanteController;
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
use App\Livewire\Pedidos\NuevoPedido;
use App\Livewire\Pedidos\Pedidos;
use App\Livewire\Pedidos\PedidosParaElaboracion;
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
    Route::get('/servicios/editar/{servicio}', NuevoServicio::class)->name('servicios.editar');

    // Módulo Pedidos
    Route::get('/pedidos', Pedidos::class)->name('pedidos');
    Route::get('/pedidos/para-elaboracion', PedidosParaElaboracion::class)->name('pedidos.para-elaboracion');
    Route::get('/pedidos/nuevo', NuevoPedido::class)->name('pedidos.nuevo');
    Route::get('/pedidos/{id}/editar', NuevoPedido::class)->name('pedidos.editar');


    // routes/web.php
    Route::get('/descargas/pedido/{comprobante}',  [DescargaComprobanteController::class, 'pedido'])
        ->name('docs.pedido.descargar');

    Route::get('/descargas/variante/{comprobante}', [DescargaComprobanteController::class, 'variante'])
        ->name('docs.variante.descargar');
    // Aquí puedes agregar otras rutas por módulo en el futuro...
});
