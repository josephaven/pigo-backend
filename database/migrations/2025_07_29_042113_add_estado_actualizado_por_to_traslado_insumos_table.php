<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('traslado_insumos', function (Blueprint $table) {
            $table->foreignId('estado_actualizado_por')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('traslado_insumos', function (Blueprint $table) {
            $table->dropForeign(['estado_actualizado_por']);
            $table->dropColumn('estado_actualizado_por');
        });
    }

};
