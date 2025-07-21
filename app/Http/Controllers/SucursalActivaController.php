<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;

class SucursalActivaController extends Controller
{
    public function cambiar(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        if (auth()->user()?->rol?->nombre !== 'Jefe') {
            abort(403, 'No autorizado');
        }


        session(['sucursal_activa_id' => $request->sucursal_id]);

        return redirect()->back()->with('success', 'Sucursal activa cambiada.');
    }
}

