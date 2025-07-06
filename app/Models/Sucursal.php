<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales'; // ✅ Esta línea fuerza la tabla correcta

    protected $fillable = [
        'nombre',
        'calle_numero',
        'colonia',
        'municipio',
        'estado',
        'telefono',
        'fecha_apertura',
        'numero_whatsapp',
    ];


    public function users()
    {
        return $this->hasMany(User::class);
    }

    // app/Models/Sucursal.php

    public function empleados()
    {
        return $this->hasMany(User::class);
    }

}


