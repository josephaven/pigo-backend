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
        Schema::create('insumo_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->string('unidad');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumo_servicios');
    }
};
