<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoInsumo extends Model
{
    use HasFactory;

    protected $table = 'movimientos_insumo';

    protected $fillable = [
        'insumo_id',
        'sucursal_id',
        'user_id',
        'tipo',
        'cantidad',
        'origen',
        'motivo',
        'fecha',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
