<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\StockSucursal;
use App\Models\MovimientoInsumo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryConsumptionService
{
    /**
     * CONSUMO AL CREAR: descuenta lo que está en pedido_insumo (ya en total).
     */
    public function consumirDesdePedido(Pedido $pedido): void
    {
        $lineas = DB::table('pedido_insumo as pi')
            ->join('pedido_servicio_variante as psv', 'psv.id', '=', 'pi.pedido_servicio_variante_id')
            ->where('psv.pedido_id', $pedido->id)
            ->selectRaw('pi.insumo_id, pi.variante_id, SUM(pi.cantidad) AS total')
            ->groupBy('pi.insumo_id', 'pi.variante_id')
            ->get();

        DB::transaction(function () use ($pedido, $lineas) {
            foreach ($lineas as $l) {
                $this->descontar(
                    sucursalId: (int) $pedido->sucursal_elaboracion_id,
                    insumoId:   $l->variante_id ? null : (int) $l->insumo_id,
                    varianteId: $l->variante_id ? (int) $l->variante_id : null,
                    cantidad:   (float) $l->total,
                    motivo:     'Pedido ' . ($pedido->folio ?? $pedido->id)
                );
            }
        });
    }

    /**
     * LEE CONSUMO AGRUPADO (insumo/variante => total) del estado actual del pedido.
     */
    public function consumoAgrupado(int $pedidoId): Collection
    {
        return DB::table('pedido_insumo as pi')
            ->join('pedido_servicio_variante as psv', 'psv.id', '=', 'pi.pedido_servicio_variante_id')
            ->where('psv.pedido_id', $pedidoId)
            ->selectRaw('pi.insumo_id, pi.variante_id, SUM(pi.cantidad) AS total')
            ->groupBy('pi.insumo_id', 'pi.variante_id')
            ->get()
            ->map(fn ($r) => (object) [
                'insumo_id'   => (int) $r->insumo_id,
                'variante_id' => $r->variante_id ? (int) $r->variante_id : null,
                'total'       => (float) $r->total,
            ]);
    }

    /**
     * DELTAS = despues - antes (array con ['i'=>insumo_id,'v'=>variante_id,'delta'=>float]).
     */
    public function calcularDeltas(Collection $antes, Collection $despues): array
    {
        $k = fn ($i, $v) => $i . '|' . ($v ?? 'null');
        $map = [];

        foreach ($antes as $r) {
            $map[$k($r->insumo_id, $r->variante_id)] = ['i' => $r->insumo_id, 'v' => $r->variante_id, 'delta' => 0 - $r->total];
        }
        foreach ($despues as $r) {
            $key = $k($r->insumo_id, $r->variante_id);
            if (!isset($map[$key])) $map[$key] = ['i' => $r->insumo_id, 'v' => $r->variante_id, 'delta' => 0];
            $map[$key]['delta'] += $r->total;
        }

        return array_values($map);
    }

    /**
     * APLICA DELTAS EN UNA SUCURSAL:
     *  delta > 0 => salida (descontar), delta < 0 => entrada (reponer).
     */
    public function aplicarDeltas(int $sucursalId, array $deltas, string $motivoBase): void
    {
        if (!$sucursalId) return;

        DB::transaction(function () use ($sucursalId, $deltas, $motivoBase) {
            foreach ($deltas as $d) {
                $insumoId   = $d['i'];
                $varianteId = $d['v'];
                $delta      = (float) $d['delta'];

                if (abs($delta) < 1e-9) continue;

                if ($delta > 0) {
                    $this->descontar($sucursalId, $varianteId ? null : $insumoId, $varianteId, $delta, $motivoBase . ' (ajuste +)');
                } else {
                    $this->reponer($sucursalId, $varianteId ? null : $insumoId, $varianteId, abs($delta), $motivoBase . ' (ajuste -)');
                }
            }
        });
    }

    /**
     * CONVENIENCIA PARA EDICIÓN:
     *  - Si cambia de sucursal: revierte TODO en la anterior y aplica TODO en la nueva.
     *  - Si misma sucursal: aplica solo deltas.
     *
     * Llama esto DESPUÉS de haber reconstruido las líneas del pedido.
     *
     * @param Pedido $pedido  Estado final (ya actualizado) del pedido.
     * @param Collection $consumoAntes  Resultado de consumoAgrupado() tomado ANTES de editar.
     * @param int $sucursalAntes        sucursal_elaboracion_id ANTES de editar.
     */
    public function ajustarPorEdicion(Pedido $pedido, Collection $consumoAntes, int $sucursalAntes): void
    {
        $consumoDespues = $this->consumoAgrupado($pedido->id);
        $sucursalDespues = (int) $pedido->sucursal_elaboracion_id;
        $motivo = 'Pedido ' . ($pedido->folio ?? $pedido->id);

        if ($sucursalAntes !== $sucursalDespues) {
            // Revertir todo en sucursal anterior
            $deltasEntrada = array_map(fn ($r) => ['i' => $r->insumo_id, 'v' => $r->variante_id, 'delta' => 0 - $r->total], $consumoAntes->all());
            $this->aplicarDeltas($sucursalAntes, $deltasEntrada, $motivo . ' (cambio sucursal: revertir)');

            // Aplicar todo en sucursal nueva
            $deltasSalida  = array_map(fn ($r) => ['i' => $r->insumo_id, 'v' => $r->variante_id, 'delta' => +$r->total], $consumoDespues->all());
            $this->aplicarDeltas($sucursalDespues, $deltasSalida, $motivo . ' (cambio sucursal: aplicar)');
        } else {
            // Misma sucursal: solo diferencias
            $deltas = $this->calcularDeltas($consumoAntes, $consumoDespues);
            $this->aplicarDeltas($sucursalDespues, $deltas, $motivo . ' (edición)');
        }
    }

    /* ===================== Helpers de stock y movimientos ===================== */

    /**
     * Salida (descontar).
     *  - Si $varianteId != null => usa variante_insumo_id y deja insumo_id NULL (por tu CHECK).
     *  - Si $varianteId == null => usa insumo_id y deja variante_insumo_id NULL.
     */
    protected function descontar(int $sucursalId, ?int $insumoId, ?int $varianteId, float $cantidad, string $motivo): void
    {
        $q = StockSucursal::where('sucursal_id', $sucursalId);
        if ($varianteId) {
            $q->whereNull('insumo_id')->where('variante_insumo_id', $varianteId);
        } else {
            $q->where('insumo_id', $insumoId)->whereNull('variante_insumo_id');
        }

        $stock = $q->lockForUpdate()->first();
        if (!$stock) {
            $stock = new StockSucursal([
                'sucursal_id'        => $sucursalId,
                'insumo_id'          => $varianteId ? null : $insumoId,
                'variante_insumo_id' => $varianteId ?: null,
                'cantidad_actual'    => 0,
                'stock_minimo'       => 0,
            ]);
        }

        $stock->cantidad_actual = (float) $stock->cantidad_actual - (float) $cantidad;
        $stock->save();

        MovimientoInsumo::create([
            'insumo_id'   => $insumoId ?? $this->inferInsumoId($varianteId),
            'sucursal_id' => $sucursalId,
            'user_id'     => Auth::id(),
            'tipo'        => 'salida',
            'cantidad'    => 0 - (int) round($cantidad), // negativo
            'origen'      => 'pedido',
            'motivo'      => $motivo,
            'fecha'       => now(),
        ]);
    }

    /**
     * Entrada (reponer).
     */
    protected function reponer(int $sucursalId, ?int $insumoId, ?int $varianteId, float $cantidad, string $motivo): void
    {
        $q = StockSucursal::where('sucursal_id', $sucursalId);
        if ($varianteId) {
            $q->whereNull('insumo_id')->where('variante_insumo_id', $varianteId);
        } else {
            $q->where('insumo_id', $insumoId)->whereNull('variante_insumo_id');
        }

        $stock = $q->lockForUpdate()->first();
        if (!$stock) {
            $stock = new StockSucursal([
                'sucursal_id'        => $sucursalId,
                'insumo_id'          => $varianteId ? null : $insumoId,
                'variante_insumo_id' => $varianteId ?: null,
                'cantidad_actual'    => 0,
                'stock_minimo'       => 0,
            ]);
        }

        $stock->cantidad_actual = (float) $stock->cantidad_actual + (float) $cantidad;
        $stock->save();

        MovimientoInsumo::create([
            'insumo_id'   => $insumoId ?? $this->inferInsumoId($varianteId),
            'sucursal_id' => $sucursalId,
            'user_id'     => Auth::id(),
            'tipo'        => 'entrada',
            'cantidad'    => (int) round($cantidad), // positivo
            'origen'      => 'pedido',
            'motivo'      => $motivo,
            'fecha'       => now(),
        ]);
    }

    /**
     * Si consumes por variante, inferimos insumo_id para registrar movimiento.
     */
    protected function inferInsumoId(?int $varianteId): ?int
    {
        if (!$varianteId) return null;
        return DB::table('variantes_insumos')->where('id', $varianteId)->value('insumo_id');
    }
}
