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
        Schema::create('comprobantes_variante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_servicio_variante_id')->constrained('pedido_servicio_variante')->onDelete('cascade');
            $table->enum('tipo', ['comprobante_pago', 'archivo_diseno']);
            $table->string('url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes_variante');
    }

};
