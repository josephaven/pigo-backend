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
        Schema::create('respuestas_campos_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_servicio_variante_id')->constrained('pedido_servicio_variante')->onDelete('cascade');
            $table->foreignId('campo_personalizado_id')->constrained('campos_personalizados')->onDelete('cascade');
            $table->text('valor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_campos_pedido');
    }

};
