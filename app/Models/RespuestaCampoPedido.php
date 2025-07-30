<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RespuestaCampoPedido extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_servicio_variante_id', 'campo_personalizado_id', 'valor'];

    public function variante() {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }

    public function campo() {
        return $this->belongsTo(CampoPersonalizado::class, 'campo_personalizado_id');
    }
}

