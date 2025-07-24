<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo_cobro',
        'precio_normal',
        'precio_maquilador',
        'precio_minimo',
        'usar_cobro_minimo',
        'activo',
    ];

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_servicio')
            ->withPivot('activo')
            ->withTimestamps();
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'insumo_servicio')
            ->withPivot('cantidad', 'unidad')
            ->withTimestamps();
    }


    public function camposPersonalizados()
    {
        return $this->hasMany(\App\Models\CampoPersonalizado::class);
    }

}
