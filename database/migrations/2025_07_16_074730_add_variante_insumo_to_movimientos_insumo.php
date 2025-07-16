<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_insumo', function (Blueprint $table) {
            $table->foreignId('variante_insumo_id')
                ->nullable()
                ->constrained('variantes_insumos')
                ->cascadeOnDelete()
                ->after('insumo_id');
        });

        // CHECK: solo uno de los dos debe estar presente (insumo o variante)
        DB::statement("
            ALTER TABLE movimientos_insumo
            ADD CONSTRAINT chk_mov_insumo_o_variante
            CHECK (
              (insumo_id IS NOT NULL AND variante_insumo_id IS NULL)
              OR
              (insumo_id IS NULL AND variante_insumo_id IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE movimientos_insumo DROP CONSTRAINT chk_mov_insumo_o_variante");

        Schema::table('movimientos_insumo', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variante_insumo_id');
        });
    }
};

