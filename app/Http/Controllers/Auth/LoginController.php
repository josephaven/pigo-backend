<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginController extends Controller implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // Puedes redirigir por rol si quieres
        if ($user->rol === 'Jefe') {
            return redirect('/configuracion/empleados');
        }

        return redirect('/dashboard'); // fallback
    }
}
