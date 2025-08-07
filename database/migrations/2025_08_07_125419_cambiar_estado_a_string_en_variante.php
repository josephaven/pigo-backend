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
        Schema::table('pedido_servicio_variante', function (Blueprint $table) {
            $table->string('estado')->default('en_espera')->change();
        });
    }

    public function down(): void
    {
        // Si quieres revertirlo, deberías volver a ENUM manualmente
        // Pero como ya no usarás ENUM, puedes dejar esto vacío
    }
};
