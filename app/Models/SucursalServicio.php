<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SucursalServicio extends Model
{
    use HasFactory;

    protected $table = 'sucursal_servicio';

    protected $fillable = [
        'servicio_id',
        'sucursal_id',
        'activo',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
