<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OpcionCampo extends Model
{
    use HasFactory;
    protected $table = 'opciones_campo';


    protected $fillable = [
        'campo_personalizado_id',
        'valor',
    ];

    public function campo()
    {
        return $this->belongsTo(CampoPersonalizado::class, 'campo_personalizado_id');
    }
}

