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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('sucursal_registro_id')->constrained('sucursales');
            $table->foreignId('sucursal_entrega_id')->constrained('sucursales');
            $table->foreignId('sucursal_elaboracion_id')->constrained('sucursales');
            $table->foreignId('user_id')->constrained();
            $table->date('fecha_entrega');
            $table->decimal('total', 10, 2);
            $table->decimal('anticipo', 10, 2)->default(0);
            $table->text('justificacion_precio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }


};
