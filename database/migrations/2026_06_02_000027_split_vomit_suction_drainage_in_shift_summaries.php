<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar los 4 campos nuevos (3 con default 0 + drainage_type nullable)
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->unsignedSmallInteger('vomit_ml')->default(0)
                ->after('vomit_suction_drainage_ml');
            $table->unsignedSmallInteger('aspiration_ml')->default(0)
                ->after('vomit_ml');
            $table->unsignedSmallInteger('drainage_ml')->default(0)
                ->after('aspiration_ml');
            $table->string('drainage_type', 200)->nullable()
                ->after('drainage_ml');
        });

        // 2. Migrar datos existentes: copiar vomit_suction_drainage_ml → vomit_ml
        DB::table('shift_summaries')
            ->whereNotNull('vomit_suction_drainage_ml')
            ->where('vomit_suction_drainage_ml', '>', 0)
            ->update([
                'vomit_ml' => DB::raw('vomit_suction_drainage_ml'),
            ]);

        // 3. Eliminar el campo viejo
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->dropColumn('vomit_suction_drainage_ml');
        });
    }

    public function down(): void
    {
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->smallInteger('vomit_suction_drainage_ml')->nullable()
                ->after('temperature');
        });

        DB::table('shift_summaries')
            ->where('vomit_ml', '>', 0)
            ->update([
                'vomit_suction_drainage_ml' => DB::raw('vomit_ml'),
            ]);

        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->dropColumn(['vomit_ml', 'aspiration_ml', 'drainage_ml', 'drainage_type']);
        });
    }
};
