<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'sucursal_registro_id', 'sucursal_entrega_id',
        'sucursal_elaboracion_id', 'user_id', 'fecha_entrega',
        'total', 'anticipo', 'justificacion_precio', 'metodo_pago_id'
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime', // o 'datetime'
    ];

    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursalRegistro() {
        return $this->belongsTo(Sucursal::class, 'sucursal_registro_id');
    }

    public function sucursalEntrega() {
        return $this->belongsTo(Sucursal::class, 'sucursal_entrega_id');
    }

    public function sucursalElaboracion() {
        return $this->belongsTo(Sucursal::class, 'sucursal_elaboracion_id');
    }

    public function variantes() {
        return $this->hasMany(PedidoServicioVariante::class);
    }

    public function comprobantes() {
        return $this->hasMany(ComprobantePedido::class);
    }

    public function factura() {
        return $this->hasOne(FacturaPedido::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id', 'id');
    }

}


