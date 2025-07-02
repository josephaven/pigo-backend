<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Redirige al módulo de Configuración directamente (o cambia según necesidad)
        return redirect()->intended('/configuracion/empleados');
    }
}
