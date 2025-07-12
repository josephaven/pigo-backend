<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleTraslado extends Model
{
    use HasFactory;

    protected $table = 'detalle_traslados';

    protected $fillable = [
        'traslado_insumo_id',
        'insumo_id',
        'variante_insumo_id',
        'cantidad',
    ];

    public function traslado()
    {
        return $this->belongsTo(TrasladoInsumo::class, 'traslado_insumo_id');
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
