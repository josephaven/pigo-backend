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
        Schema::create('historial_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_servicio_variante_id')->constrained('pedido_servicio_variante')->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->enum('nuevo_estado', ['en_espera', 'en_produccion', 'entregado', 'cancelado']);
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_pedidos');
    }

};
