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
        Schema::create('opciones_campo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_personalizado_id')->constrained('campos_personalizados')->cascadeOnDelete();
            $table->string('valor');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opcion_campos');
    }
};
