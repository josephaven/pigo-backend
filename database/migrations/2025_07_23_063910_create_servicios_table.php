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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo_cobro', ['pieza', 'm2', 'ml', 'otro'])->default('pieza');
            $table->decimal('precio_normal', 10, 2);
            $table->decimal('precio_maquilador', 10, 2);
            $table->decimal('precio_minimo', 10, 2)->nullable();
            $table->boolean('usar_cobro_minimo')->default(false);
            $table->boolean('activo')->default(true); // Estado general
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
