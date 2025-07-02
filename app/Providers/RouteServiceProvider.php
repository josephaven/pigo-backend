<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Esta es la ruta a la que los usuarios son redirigidos después de iniciar sesión.
     *
     * @var string
     */
    public const HOME = '/configuracion/empleados'; // <- Cámbialo según el módulo que quieras mostrar tras login

    /**
     * Define tus rutas aquí.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
