<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoServicioVariante extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id', 'servicio_id', 'nombre_personalizado', 'descripcion',
        'atributos', 'cantidad', 'precio_unitario', 'subtotal',
        'nota_disenio', 'estado'
    ];

    protected $casts = [
        'atributos' => 'array',
    ];

    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }

    public function servicio() {
        return $this->belongsTo(Servicio::class);
    }

    public function insumos() {
        return $this->hasMany(PedidoInsumo::class);
    }

    public function comprobantes() {
        return $this->hasMany(ComprobanteVariante::class);
    }

    public function respuestasCampos() {
        return $this->hasMany(RespuestaCampoPedido::class);
    }

    public function historial() {
        return $this->hasMany(HistorialPedido::class);
    }
}

