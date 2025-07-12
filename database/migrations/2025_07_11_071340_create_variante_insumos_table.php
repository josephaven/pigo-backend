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
        Schema::create('variantes_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->json('atributos'); // Ejemplo: {"talla": "M", "color": "Rojo"}
            $table->string('codigo_interno')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variantes_insumos');
    }
};
