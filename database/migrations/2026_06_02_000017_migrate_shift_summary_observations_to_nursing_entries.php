<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: migrar las observaciones existentes a nursing_entries.
        // OJO: en shift_summaries la columna del autor es `recorded_by`.
        $summaries = DB::table('shift_summaries')
            ->whereNotNull('observations')
            ->where('observations', '!=', '')
            ->get();

        foreach ($summaries as $summary) {
            // Sin autor no podemos cumplir la FK NOT NULL recorded_by_id: omitimos.
            if (empty($summary->recorded_by)) {
                continue;
            }

            // Timestamp representativo dentro del rango de cada turno.
            $recordedAt = match ($summary->shift) {
                'morning' => \Carbon\Carbon::parse($summary->shift_date)->setTime(10, 0),
                'evening' => \Carbon\Carbon::parse($summary->shift_date)->setTime(17, 0),
                'night'   => \Carbon\Carbon::parse($summary->shift_date)->setTime(23, 0),
                default   => \Carbon\Carbon::parse($summary->shift_date)->setTime(12, 0),
            };

            DB::table('nursing_entries')->insert([
                'stay_id'        => $summary->stay_id,
                'category'       => 'observation',
                'description'    => $summary->observations,
                'recorded_at'    => $recordedAt,
                'shift'          => $summary->shift,
                'shift_date'     => $summary->shift_date,
                'recorded_by_id' => $summary->recorded_by,
                'created_at'     => $summary->updated_at ?? now(),
                'updated_at'     => $summary->updated_at ?? now(),
            ]);
        }

        // Paso 2: eliminar la columna observations de shift_summaries.
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->dropColumn('observations');
        });
    }

    public function down(): void
    {
        Schema::table('shift_summaries', function (Blueprint $table) {
            $table->text('observations')->nullable();
        });

        // No restauramos los datos individuales: ya viven en nursing_entries.
        // El down solo recrea la columna vacía.
    }
};
