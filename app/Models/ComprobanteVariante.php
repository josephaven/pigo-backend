<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteVariante extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_servicio_variante_id', 'tipo', 'url'];

    public function variante() {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }
}
