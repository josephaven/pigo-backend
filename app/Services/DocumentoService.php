<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ComprobantePedido;
use App\Models\ComprobanteVariante;

class DocumentoService
{
    protected string $disk = 'wasabi';

    /** Guarda el archivo usando el propio objeto (Livewire-safe) */
    protected function put(TemporaryUploadedFile|UploadedFile $file, string $subdir): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['pdf','cdr'])) {
            throw new \RuntimeException('Solo PDF/CDR');
        }

        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = Str::slug($base) . '-' . now()->format('YmdHis') . '-' . Str::random(5) . '.' . $ext;
        $dir  = trim($subdir, '/');

        // ✅ Calcula checksum ANTES de mover el tmp
        $checksum = @hash_file('sha256', $file->getRealPath()) ?: null;

        // ✅ Usa storeAs (mueve desde livewire-tmp y crea carpeta)
        $path = $file->storeAs($dir, $name, [
            'disk'       => $this->disk,
            'visibility' => 'private',
            'ContentType'=> $mime,
        ]);
        // $path es relativo al disk (p.ej. "pedidos/13/archivo.pdf")

        return [
            'disk'          => $this->disk,
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $mime,
            'size'          => $file->getSize(),
            'checksum'      => $checksum,
        ];
    }

    public function subirParaPedido(int $pedidoId, string $tipo, UploadedFile|TemporaryUploadedFile $file)
    {
        $meta = $this->put($file, "pedidos/{$pedidoId}");
        return ComprobantePedido::create(array_merge([
            'pedido_id' => $pedidoId,
            'tipo'      => $tipo,   // 'comprobante_pago' | 'archivo_diseno'
            'url'       => null,    // legacy
        ], $meta));
    }

    public function subirParaVariante(int $psvId, string $tipo, UploadedFile|TemporaryUploadedFile $file)
    {
        $meta = $this->put($file, "variantes/{$psvId}");
        return ComprobanteVariante::create(array_merge([
            'pedido_servicio_variante_id' => $psvId,
            'tipo'      => $tipo,
            'url'       => null,
        ], $meta));
    }

    private function sanitizeFilename(string $name): string
    {
        return str_replace(['"', "\r", "\n"], ['\'', '', ''], $name);
    }

    public function urlDescarga(string $disk, string $path, string $filename, ?string $mime = null, int $minutes = 10): string
    {
        $safeName = $this->sanitizeFilename($filename);

        $opts = ['ResponseContentDisposition' => 'attachment; filename="'.$safeName.'"'];
        if ($mime) $opts['ResponseContentType'] = $mime;

        // S3/Wasabi soportan temporaryUrl; si no, cae al url() público
        try {
            return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes($minutes), $opts);
        } catch (\Throwable $e) {
            return Storage::disk($disk)->url($path);
        }
    }

    public function subirYDevolverMeta(UploadedFile|TemporaryUploadedFile $file, string $subdir): array
    {
        return $this->put($file, $subdir);
    }

    public function crearRegistrosVarianteDesdeMeta(array $psvIds, string $tipo, array $meta): void
    {
        foreach ($psvIds as $psvId) {
            ComprobanteVariante::create(array_merge([
                'pedido_servicio_variante_id' => $psvId,
                'tipo' => $tipo,
                'url'  => null,
            ], $meta));
        }
    }
}
