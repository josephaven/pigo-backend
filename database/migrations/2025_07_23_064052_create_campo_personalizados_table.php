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
        Schema::create('campos_personalizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('tipo', ['texto', 'numerico', 'booleano', 'select']);
            $table->boolean('requerido')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campo_personalizados');
    }
};
