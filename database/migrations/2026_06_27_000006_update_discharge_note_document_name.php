<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('documents')
            ->where('code', 'discharge_note')
            ->update([
                'name'        => 'Nota de Alta',
                'description' => 'Documento de cierre clínico al alta del paciente.',
                'icon'        => 'bi-box-arrow-right',
            ]);
    }

    public function down(): void
    {
        DB::table('documents')
            ->where('code', 'discharge_note')
            ->update([
                'name'        => 'Nota de Egreso',
                'description' => 'Resumen clínico al alta del paciente.',
                'icon'        => 'bi-box-arrow-right',
            ]);
    }
};
