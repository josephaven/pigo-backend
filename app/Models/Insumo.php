<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'unidad_medida',
        'categoria_insumo_id',
        'tiene_variantes',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_insumo_id');
    }

    public function variantes()
    {
        return $this->hasMany(VarianteInsumo::class, 'insumo_id');
    }

    public function stockSucursales()
    {
        return $this->hasMany(StockSucursal::class, 'insumo_id');
    }

    public function mermas()
    {
        return $this->hasMany(Merma::class, 'insumo_id');
    }

    public function detalleTraslados()
    {
        return $this->hasMany(DetalleTraslado::class, 'insumo_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInsumo::class, 'insumo_id');
    }

    // en App\Models\Insumo.php
    public function stockActual(int $sucursalId): int
    {
        return $this->stockSucursales()
            ->where('sucursal_id', $sucursalId)
            ->value('cantidad_actual') ?? 0;
    }

    public function mermaTotal(int $sucursalId): int
    {
        return $this->mermas()
            ->where('sucursal_id', $sucursalId)
            ->sum('cantidad') ?? 0;
    }

    public function getStockDeSucursalAttribute()
    {
        $sucursalId = sucursal_activa_id(); // ✅ Usamos el helper correcto
        return $this->stockSucursales->firstWhere('sucursal_id', $sucursalId)?->cantidad_actual ?? 0;
    }

    public function getAlertaStockAttribute(): string
    {
        $sucursalId = sucursal_activa_id(); // ✅ Lo mismo aquí

        $registro = $this->stockSucursales->firstWhere('sucursal_id', $sucursalId);

        if (!$registro) {
            return 'Sin stock';
        }

        $actual = (int) $registro->cantidad_actual;
        $minimo = (int) $registro->stock_minimo;

        if ($actual === 0) return 'Sin stock';
        if ($actual < $minimo) return 'Bajo stock';

        return 'Normal';
    }





}
