<?php

// database/migrations/xxxx_xx_xx_xxxxxx_alter_comprobantes_add_storage_columns.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comprobantes_pedido', function (Blueprint $table) {
            $table->string('disk')->nullable()->after('tipo');
            $table->string('path')->nullable()->after('disk');
            $table->string('original_name')->nullable()->after('path');
            $table->string('mime')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->default(0)->after('mime');
            $table->string('checksum', 64)->nullable()->after('size');
            $table->string('url')->nullable()->change(); // por si antes era NOT NULL
        });

        Schema::table('comprobantes_variante', function (Blueprint $table) {
            $table->string('disk')->nullable()->after('tipo');
            $table->string('path')->nullable()->after('disk');
            $table->string('original_name')->nullable()->after('path');
            $table->string('mime')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->default(0)->after('mime');
            $table->string('checksum', 64)->nullable()->after('size');
            $table->string('url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('comprobantes_pedido', function (Blueprint $table) {
            $table->dropColumn(['disk','path','original_name','mime','size','checksum']);
            // no revertimos url nullable por seguridad
        });
        Schema::table('comprobantes_variante', function (Blueprint $table) {
            $table->dropColumn(['disk','path','original_name','mime','size','checksum']);
        });
    }
};

