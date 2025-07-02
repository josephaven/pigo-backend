<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Empleado extends Authenticatable
{
    protected $fillable = [
        'nombre',
        'email', // usuario
        'password',
        'rol',
        'sucursal',
        'salario',
        'activo',
    ];

    protected $hidden = [
        'password',
    ];
}
