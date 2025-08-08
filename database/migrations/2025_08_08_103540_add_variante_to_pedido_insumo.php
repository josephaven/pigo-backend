<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('pedido_insumo', function (Blueprint $t) {
            $t->foreignId('variante_id')->nullable()->constrained('variantes_insumos')->nullOnDelete();
            $t->json('atributos')->nullable();
        });
    }
    public function down(): void {
        Schema::table('pedido_insumo', function (Blueprint $t) {
            $t->dropConstrainedForeignId('variante_id');
            $t->dropColumn('atributos');
        });
    }
};
