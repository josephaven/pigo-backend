<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Empleado que lo registró
            $table->enum('tipo', ['entrada', 'salida', 'merma', 'traslado'])->index();
            $table->integer('cantidad'); // positiva o negativa según tipo
            $table->string('origen')->nullable(); // Pedido #34, Traslado #2
            $table->text('motivo')->nullable(); // opcional para detalles
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_insumo');
    }
};
