<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InsumoServicio extends Model
{
    use HasFactory;

    protected $table = 'insumo_servicio';

    protected $fillable = [
        'servicio_id',
        'insumo_id',
        'cantidad',
        'unidad',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}

