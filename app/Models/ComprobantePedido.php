<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComprobantePedido extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_id', 'tipo', 'url'];

    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }
}

