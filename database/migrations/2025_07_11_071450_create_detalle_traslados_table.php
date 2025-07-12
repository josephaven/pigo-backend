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
        Schema::create('detalle_traslados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_insumo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insumo_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('variante_insumo_id')->nullable()->constrained('variantes_insumos')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_traslados');
    }
};
