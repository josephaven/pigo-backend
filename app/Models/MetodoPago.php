<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetodoPago extends Model
{
    protected $table = 'metodo_pagos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'banco',
        'cuenta',
        'clabe',
        'titular',
    ];
}

