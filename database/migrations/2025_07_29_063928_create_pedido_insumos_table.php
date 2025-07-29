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
        Schema::create('pedido_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_servicio_variante_id')->constrained('pedido_servicio_variante')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained()->onDelete('restrict');
            $table->string('unidad');
            $table->decimal('cantidad', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_insumo');
    }

};
