<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacturaPedido extends Model
{
    use HasFactory;

    protected $fillable = ['pedido_id', 'rfc', 'razon_social', 'direccion', 'uso_cfdi', 'metodo_pago'];
    protected $table = 'facturas_pedido';


    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }
}

