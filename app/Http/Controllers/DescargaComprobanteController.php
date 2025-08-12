<?php

// app/Http/Controllers/DescargaComprobanteController.php
namespace App\Http\Controllers;

use App\Models\ComprobantePedido;
use App\Models\ComprobanteVariante;
use App\Services\DocumentoService;

class DescargaComprobanteController extends Controller
{
    public function pedido(ComprobantePedido $comprobante, DocumentoService $docs)
    {
        // TODO: $this->authorize('view', $comprobante);
        $url = $docs->urlDescarga(
            $comprobante->disk, $comprobante->path,
            $comprobante->original_name, $comprobante->mime
        );
        return redirect()->away($url);
    }

    public function variante(ComprobanteVariante $comprobante, DocumentoService $docs)
    {
        try {
            $url = $docs->urlDescarga($comprobante->disk, $comprobante->path, $comprobante->original_name, $comprobante->mime);
            return redirect()->away($url);
        } catch (\Throwable $e) {
            // fallback: stream por el servidor
            return \Storage::disk($comprobante->disk)
                ->download($comprobante->path, $comprobante->original_name);
        }
    }

}
