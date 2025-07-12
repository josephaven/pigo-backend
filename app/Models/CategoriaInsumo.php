<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaInsumo extends Model
{
    use HasFactory;

    protected $table = 'categoria_insumos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'categoria_insumo_id');
    }
}
