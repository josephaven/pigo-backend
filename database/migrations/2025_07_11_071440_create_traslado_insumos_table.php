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
        Schema::create('traslado_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_origen_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('sucursal_destino_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable();
            $table->enum('estado', ['pendiente', 'enviado', 'recibido', 'cancelado'])->default('pendiente');
            $table->date('fecha_solicitud');
            $table->date('fecha_entrega')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traslado_insumos');
    }
};
