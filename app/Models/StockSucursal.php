<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockSucursal extends Model
{
    use HasFactory;

    protected $table = 'stock_sucursales';

    protected $fillable = [
        'sucursal_id',
        'insumo_id',
        'variante_insumo_id',
        'cantidad_actual',
        'stock_minimo',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function variante()
    {
        return $this->belongsTo(VarianteInsumo::class, 'variante_insumo_id');
    }
}
