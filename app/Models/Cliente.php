<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_completo',
        'telefono',
        'tipo_cliente',
        'ocupacion',
        'fecha_nacimiento',
        'sucursal_id',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}

