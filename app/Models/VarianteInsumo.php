<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VarianteInsumo extends Model
{
    use HasFactory;

    protected $table = 'variantes_insumos';

    protected $fillable = [
        'insumo_id',
        'atributos',
        'codigo_interno',
    ];

    protected $casts = [
        'atributos' => 'array',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function stockSucursales()
    {
        return $this->hasMany(StockSucursal::class, 'variante_insumo_id');
    }

    public function mermas()
    {
        return $this->hasMany(Merma::class, 'variante_insumo_id');
    }

    public function detalleTraslados()
    {
        return $this->hasMany(DetalleTraslado::class, 'variante_insumo_id');
    }
}
