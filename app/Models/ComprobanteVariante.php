<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComprobanteVariante extends Model
{
    use HasFactory;

    protected $table = 'comprobantes_variante';

    protected $fillable = [
        'pedido_servicio_variante_id',
        'tipo',
        'url',            // legacy
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'checksum',
    ];

    public function variante() {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }
}
