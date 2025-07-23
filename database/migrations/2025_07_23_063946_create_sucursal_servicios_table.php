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
        Schema::create('sucursal_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->boolean('activo')->default(true); // Estado por sucursal
            $table->timestamps();

            $table->unique(['servicio_id', 'sucursal_id']); // evita duplicados
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal_servicios');
    }
};
