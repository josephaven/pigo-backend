<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampoPersonalizado extends Model
{
    use HasFactory;

    protected $table = 'campos_personalizados';

    protected $fillable = [
        'servicio_id',
        'nombre',
        'tipo',
        'requerido',
        'orden',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function opciones()
    {
        return $this->hasMany(\App\Models\OpcionCampo::class);
    }

}

