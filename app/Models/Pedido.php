<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'sucursal_registro_id',
        'sucursal_entrega_id',
        'sucursal_elaboracion_id',
        'user_id',
        'fecha_entrega',
        'total',
        'anticipo',
        'justificacion_precio',
        'metodo_pago_id',
        'folio_num',   // <- nuevo
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Pedido $pedido) {
            // Asignar correlativo por sucursal si no viene
            if (empty($pedido->folio_num) && !empty($pedido->sucursal_registro_id)) {
                $pedido->folio_num = static::siguienteNumeroParaSucursal((int)$pedido->sucursal_registro_id);
            }
        });
    }

    /** Calcula el siguiente correlativo de forma atómica. */
    public static function siguienteNumeroParaSucursal(int $sucursalId): int
    {
        return DB::transaction(function () use ($sucursalId) {
            // Bloqueo lógico por sucursal para esta transacción
            DB::statement('SELECT pg_advisory_xact_lock(?)', [$sucursalId]);

            $ultimo = DB::table('pedidos')
                ->where('sucursal_registro_id', $sucursalId)
                ->max('folio_num');

            return ($ultimo ?? 0) + 1;
        });
    }


    /** Accessor: folio visible dinámico = CODIGO + '-' + folio_num con padding. */
    public function getFolioAttribute(): string
    {
        $codigo = $this->sucursalRegistro->codigo ?? 'SUC';
        $codigo = preg_replace('/[^A-Z0-9]/', '', strtoupper($codigo)) ?: 'SUC';
        $num    = (int) $this->folio_num;
        return $codigo . '-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }


    // Relaciones
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
