<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\StockSucursal;
use App\Models\TrasladoInsumo;
use App\Models\DetalleTraslado;
use App\Models\MovimientoInsumo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class PedidoService
{
    /**
     * Procesa los ítems de un pedido, genera traslados automáticos
     * si una sucursal no tiene stock suficiente.
     *
     * @param  Pedido  $pedido       Pedido ya creado con sucursal asignada.
     * @param  array   $items        Cada ítem: ['insumo_id'=>…, 'variante_insumo_id'=>…, 'cantidad'=>…]
     * @return void
     *
     * @throws \Exception Si no hay stock en ninguna sucursal.
     */
    public function procesar(Pedido $pedido, array $items): void
    {
        DB::transaction(function() use ($pedido, $items) {
            $sucursalDestino = $pedido->sucursal_id;

            foreach ($items as $item) {
                // 1) Chequeamos stock en la sucursal destino
                $query = StockSucursal::where('sucursal_id', $sucursalDestino)
                    ->where('insumo_id', $item['insumo_id'])
                    ->when(
                        isset($item['variante_insumo_id']),
                        fn($q) => $q->where('variante_insumo_id', $item['variante_insumo_id'])
                    )
                    ->firstOrFail();

                $faltante = max(0, $item['cantidad'] - $query->cantidad_actual);
                if ($faltante > 0) {
                    // 2) Buscamos otra sucursal con stock
                    $stockOrigen = $this->buscarSucursalConStock(
                        $item['insumo_id'],
                        $item['variante_insumo_id'] ?? null,
                        $faltante
                    );

                    if (! $stockOrigen) {
                        throw new \Exception("No hay stock suficiente para el insumo {$item['insumo_id']}.");
                    }

                    // 3) Creamos el traslado
                    $traslado = TrasladoInsumo::create([
                        'sucursal_origen_id'  => $stockOrigen->sucursal_id,
                        'sucursal_destino_id' => $sucursalDestino,
                        'user_id'             => auth()->id(),
                        'estado'              => 'pendiente',
                        'fecha_solicitud'     => now()->toDateString(),
                    ]);

                    // 4) Detalle del traslado
                    DetalleTraslado::create([
                        'traslado_insumo_id'  => $traslado->id,
                        'insumo_id'           => $item['insumo_id'],
                        'variante_insumo_id'  => $item['variante_insumo_id'] ?? null,
                        'cantidad'            => $faltante,
                    ]);

                    // 5) Ajustamos stock en ambas sucursales
                    $stockOrigen->decrement('cantidad_actual', $faltante);
                    $query->increment('cantidad_actual', $faltante);

                    // 6) Guardamos movimientos de auditoría
                    MovimientoInsumo::create([
                        'insumo_id'   => $item['insumo_id'],
                        'sucursal_id' => $stockOrigen->sucursal_id,
                        'user_id'     => auth()->id(),
                        'tipo'        => 'traslado',
                        'cantidad'    => -$faltante,
                        'origen'      => "Traslado #{$traslado->id}",
                    ]);
                    MovimientoInsumo::create([
                        'insumo_id'   => $item['insumo_id'],
                        'sucursal_id' => $sucursalDestino,
                        'user_id'     => auth()->id(),
                        'tipo'        => 'traslado',
                        'cantidad'    => $faltante,
                        'origen'      => "Traslado #{$traslado->id}",
                    ]);
                }

                // 7) Finalmente, agrega el detalle al pedido
                $pedido->detalles()->create([
                    'insumo_id'          => $item['insumo_id'],
                    'variante_insumo_id' => $item['variante_insumo_id'] ?? null,
                    'cantidad'           => $item['cantidad'],
                    // otros campos que necesites (precio, subtotal, etc.)
                ]);
            }
        });
    }

    /**
     * Busca la primera sucursal (distinta de la destino) que tenga
     * stock suficiente para cubrir la cantidad faltante.
     *
     * @param  int         $insumoId
     * @param  int|null    $varianteId
     * @param  float       $cantidad
     * @return StockSucursal|null
     */
    protected function buscarSucursalConStock(int $insumoId, ?int $varianteId, float $cantidad): ?StockSucursal
    {
        return StockSucursal::where('insumo_id', $insumoId)
            ->when($varianteId, fn($q) => $q->where('variante_insumo_id', $varianteId))
            ->where('sucursal_id', '!=', auth()->user()->sucursal_id)
            ->where('cantidad_actual', '>=', $cantidad)
            ->orderByDesc('cantidad_actual')
            ->first();
    }
}
