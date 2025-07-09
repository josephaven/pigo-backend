<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function enviarMensaje($numero, $mensaje)
    {
        $token = config('services.whatsapp.token');
        $phone_id = config('services.whatsapp.phone_id');
        $version = config('services.whatsapp.version', 'v18.0');

        // Si no hay token configurado, simulamos
        if (!$token || !$phone_id) {
            Log::info("🔧 Simulación: se enviaría mensaje a {$numero}: '{$mensaje}'");
            return [
                'ok' => false,
                'simulado' => true,
                'mensaje' => $mensaje
            ];
        }

        $response = Http::withToken($token)->post("https://graph.facebook.com/{$version}/{$phone_id}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $numero,
            'type' => 'text',
            'text' => ['body' => $mensaje],
        ]);

        if ($response->successful()) {
            Log::info("✅ Mensaje enviado correctamente a {$numero}");
            return ['ok' => true, 'data' => $response->json()];
        } else {
            Log::error("❌ Error al enviar mensaje a {$numero}: " . $response->body());
            return ['ok' => false, 'error' => $response->body()];
        }
    }
}
