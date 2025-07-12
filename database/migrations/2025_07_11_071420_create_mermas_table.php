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
        Schema::create('mermas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Empleado responsable
            $table->foreignId('insumo_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('variante_insumo_id')->nullable()->constrained('variantes_insumos')->cascadeOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->text('justificacion');
            $table->date('fecha');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mermas');
    }
};
