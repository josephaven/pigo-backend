<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistorialPedido extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_servicio_variante_id', 'user_id', 'nuevo_estado', 'motivo'];

    public function variante() {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }

    public function usuario() {
        return $this->belongsTo(User::class, 'user_id');
    }
}

