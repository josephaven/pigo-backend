<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EstablecerSucursalActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            if ($user->rol?->nombre === 'Administrador Principal') {
                // El jefe puede cambiar de sucursal, usar la guardada en sesión o default
                if (!session()->has('sucursal_activa_id')) {
                    session(['sucursal_activa_id' => $user->sucursal_id]); // Puede ser null
                }
            } else {
                // Otros roles deben usar su sucursal asignada
                session(['sucursal_activa_id' => $user->sucursal_id]);
            }
        }

        return $next($request);
    }
}

