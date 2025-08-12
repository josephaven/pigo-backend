<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComprobantePedido extends Model
{
    use HasFactory;

    protected $table = 'comprobantes_pedido';

    protected $fillable = [
        'pedido_id',
        'tipo',
        'url',            // legacy
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'checksum',
    ];

    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }
}
