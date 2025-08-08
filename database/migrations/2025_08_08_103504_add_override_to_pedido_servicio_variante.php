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
        Schema::table('pedido_servicio_variante', function (Blueprint $t) {
            $t->decimal('total_final', 10, 2)->nullable()->after('subtotal');
            $t->string('justificacion_total')->nullable()->after('total_final');
        });
    }
    public function down(): void {
        Schema::table('pedido_servicio_variante', function (Blueprint $t) {
            $t->dropColumn(['total_final','justificacion_total']);
        });
    }
};
