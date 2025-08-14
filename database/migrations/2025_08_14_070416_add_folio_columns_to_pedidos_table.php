<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Nuevo correlativo por sucursal y folio visible
            $table->integer('folio_num')->nullable();       // p.ej. 1, 2, 3...
            $table->string('folio', 50)->nullable();        // p.ej. "COATZ-0001"

            // Reglas de unicidad:
            // - folio_num es único dentro de la misma sucursal de registro
            // - folio completo es único a nivel global (útil para búsquedas)
            $table->unique(['sucursal_registro_id', 'folio_num'], 'pedidos_sucursal_folio_num_unique');
            $table->unique('folio', 'pedidos_folio_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Quitar índices únicos antes de eliminar columnas
            $table->dropUnique('pedidos_sucursal_folio_num_unique');
            $table->dropUnique('pedidos_folio_unique');

            $table->dropColumn(['folio_num', 'folio']);
        });
    }
};
