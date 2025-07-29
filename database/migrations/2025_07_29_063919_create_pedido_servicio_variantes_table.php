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
        Schema::create('pedido_servicio_variante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
            $table->foreignId('servicio_id')->nullable()->constrained()->nullOnDelete();

            $table->string('nombre_personalizado')->nullable();
            $table->text('descripcion')->nullable();
            $table->json('atributos')->nullable();

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('nota_disenio')->nullable();

            $table->enum('estado', ['en_espera', 'en_produccion', 'entregado', 'cancelado'])->default('en_espera');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_servicio_variante');
    }


};
