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
        Schema::table('metodo_pagos', function (Blueprint $table) {
            $table->enum('tipo', ['efectivo', 'transferencia', 'terminal'])->default('efectivo')->after('descripcion');
            $table->string('banco')->nullable()->after('tipo');
            $table->string('cuenta')->nullable()->after('banco');
            $table->string('clabe')->nullable()->after('cuenta');
            $table->string('titular')->nullable()->after('clabe');
        });
    }

    public function down(): void
    {
        Schema::table('metodo_pagos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'banco', 'cuenta', 'clabe', 'titular']);
        });
    }

};
