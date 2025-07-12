<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_sucursales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('insumo_id')
                ->nullable()
                ->constrained('insumos')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('variante_insumo_id')
                ->nullable()
                ->constrained('variantes_insumos')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('cantidad_actual', 10, 2)->default(0);
            $table->decimal('stock_minimo',    10, 2)->default(0);

            // Evita duplicados
            $table->unique(
                ['sucursal_id', 'insumo_id', 'variante_insumo_id'],
                'stock_sucursal_unico'
            );
        });

        // Opcional: fuerza que solo uno de los dos FK esté presente
        DB::statement("
      ALTER TABLE stock_sucursales
      ADD CONSTRAINT chk_insumo_o_variante
      CHECK (
        (insumo_id IS NOT NULL AND variante_insumo_id IS NULL)
        OR
        (insumo_id IS NULL AND variante_insumo_id IS NOT NULL)
      )
    ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_sucursales');
    }

};
