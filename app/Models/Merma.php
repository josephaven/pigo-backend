<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Merma extends Model
{
    use HasFactory;

    protected $table = 'mermas';

    protected $fillable = [
        'sucursal_id',
        'user_id',
        'insumo_id',
        'variante_insumo_id',
        'cantidad',
        'justificacion',
        'fecha',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
