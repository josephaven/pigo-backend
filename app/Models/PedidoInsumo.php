<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoInsumo extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_servicio_variante_id', 'insumo_id', 'unidad', 'cantidad'];

    public function variante() {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }

    public function insumo() {
        return $this->belongsTo(Insumo::class);
    }
}

