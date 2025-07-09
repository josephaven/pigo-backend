<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    public function enviar(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'telefono' => ['required', 'string'],
            'mensaje' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errores' => $validator->errors(),
            ], 422);
        }

        // Enviar mensaje
        $whatsapp = new WhatsAppService();
        $resultado = $whatsapp->enviarMensaje($request->telefono, $request->mensaje);

        return response()->json($resultado);
    }
}
