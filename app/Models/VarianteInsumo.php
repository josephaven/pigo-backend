<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VarianteInsumo extends Model
{
    use HasFactory;

    protected $table = 'variantes_insumos';

    protected $fillable = [
        'insumo_id',
        'atributos',      // opcional si guardas JSON además de la tabla detalle
        'codigo_interno',
    ];

    protected $casts = [
        'atributos' => 'array',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function stockSucursales()
    {
        return $this->hasMany(StockSucursal::class, 'variante_insumo_id', 'id');
    }

    public function mermas()
    {
        return $this->hasMany(Merma::class, 'variante_insumo_id');
    }

    public function detalleTraslados()
    {
        return $this->hasMany(DetalleTraslado::class, 'variante_insumo_id');
    }

    /**
     * 👇 Relación que te falta (la que usa el componente):
     * variante -> (muchos) atributo_valor
     */
    public function atributosValores()
    {
        // Ajusta el nombre de la clase y de la tabla si en tu BD se llaman distinto
        return $this->hasMany(VarianteInsumoAtributoValor::class, 'variante_insumo_id');
    }
}
