<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrasladoInsumo extends Model
{
    use HasFactory;

    protected $table = 'traslado_insumos';

    protected $fillable = [
        'sucursal_origen_id',
        'sucursal_destino_id',
        'user_id',
        'pedido_id',
        'estado',
        'fecha_solicitud',
        'fecha_entrega',
        'estado_actualizado_por',
    ];

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleTraslado::class, 'traslado_insumo_id');
    }

    public function estadoActualizadoPor()
    {
        return $this->belongsTo(User::class, 'estado_actualizado_por');
    }

}
