<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobantePedido extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_id', 'tipo', 'url'];

    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }
}

