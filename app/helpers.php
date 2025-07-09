<?php

use Illuminate\Support\Facades\Config;
use App\Services\WhatsAppService;

if (!function_exists('enviarMensajeWhatsApp')) {
    /**
     * Envía un mensaje de WhatsApp usando una clave de mensaje definida en config/whatsapp-mensajes.php
     *
     * @param string $telefono Número en formato internacional (ej: 5219991234567)
     * @param string $claveMensaje Clave del mensaje en config/whatsapp-mensajes.php
     * @param array $valoresExtra Valores opcionales para reemplazo dinámico en el mensaje
     * @return array Resultado del intento de envío
     */
    function enviarMensajeWhatsApp($telefono, $claveMensaje, $valoresExtra = [])
    {
        $mensaje = Config::get("whatsapp-mensajes.$claveMensaje");

        if (!$mensaje) {
            return [
                'ok' => false,
                'error' => 'Mensaje no encontrado en whatsapp-mensajes.php',
                'clave' => $claveMensaje,
            ];
        }

        // Reemplazo de variables tipo {nombre}, {total}, etc.
        foreach ($valoresExtra as $clave => $valor) {
            $mensaje = str_replace("{{$clave}}", $valor, $mensaje);
        }

        return (new WhatsAppService())->enviarMensaje($telefono, $mensaje);
    }
}
