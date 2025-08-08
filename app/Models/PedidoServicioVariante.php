<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoServicioVariante extends Model
{
    use HasFactory;

    protected $table = 'pedido_servicio_variante';

    // 🔹 Agrega override y justificación
    protected $fillable = [
        'pedido_id',
        'servicio_id',
        'nombre_personalizado',
        'descripcion',
        'atributos',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'total_final',          // 👈
        'justificacion_total',  // 👈
        'nota_disenio',
        'estado',
    ];

    protected $casts = [
        'atributos'       => 'array',
        'cantidad'        => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'total_final'     => 'decimal:2',
    ];

    // Opcional: constants para estado (coinciden con tu enum)
    public const EST_EN_ESPERA     = 'en_espera';
    public const EST_EN_PRODUCCION = 'en_produccion';
    public const EST_ENTREGADO     = 'entregado';
    public const EST_CANCELADO     = 'cancelado';

    // -------- Relaciones --------
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function insumos()
    {
        // FK real en pedido_insumo
        return $this->hasMany(PedidoInsumo::class, 'pedido_servicio_variante_id');
    }

    public function comprobantes()
    {
        // Ajusta el FK si tu tabla lo nombra distinto
        return $this->hasMany(ComprobanteVariante::class, 'pedido_servicio_variante_id');
    }

    public function respuestasCampos()
    {
        return $this->hasMany(RespuestaCampoPedido::class, 'pedido_servicio_variante_id')
            ->with('campo'); // ya que lo usas en with()
    }

    public function historial()
    {
        // Verifica que HistorialPedido tenga FK 'pedido_servicio_variante_id'
        return $this->hasMany(HistorialPedido::class, 'pedido_servicio_variante_id');
    }
}
