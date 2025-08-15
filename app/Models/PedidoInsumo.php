<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// App\Models\PedidoInsumo.php
class PedidoInsumo extends Model
{
    use HasFactory;

    protected $table = 'pedido_insumo';

    protected $fillable = [
        'pedido_servicio_variante_id',
        'insumo_id',
        'unidad',
        'cantidad',
        'variante_id',   // ✔ existe por tu migración
        'atributos',     // ✔ existe por tu migración
    ];

    protected $casts = [
        'atributos' => 'array',
        'cantidad'  => 'decimal:2',
    ];

    // ❗ Este método en tu código apunta a PedidoServicioVariante, pero se llama "variante":
    public function variante()
    {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }

    // ✅ Nombres explícitos (recomendado):
    public function pedidoVariante()
    {
        return $this->belongsTo(PedidoServicioVariante::class, 'pedido_servicio_variante_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function varianteInsumo()
    {
        // Relación real hacia la variante de insumo
        return $this->belongsTo(VarianteInsumo::class, 'variante_id');
    }
}
