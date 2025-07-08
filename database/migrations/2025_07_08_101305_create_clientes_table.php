<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('telefono')->unique();
            $table->enum('tipo_cliente', ['Normal', 'Frecuente', 'Maquilador'])->default('Normal');
            $table->string('ocupacion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
