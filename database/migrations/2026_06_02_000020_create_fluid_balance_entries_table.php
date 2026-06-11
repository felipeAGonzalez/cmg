<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fluid_balance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fluid_balance_day_id')->constrained()->cascadeOnDelete();

            $table->timestamp('recorded_at'); // momento de la toma
            $table->string('shift', 20);      // calculado desde recorded_at
            $table->date('shift_date');

            // Ingresos (ml) — 6 columnas
            $table->unsignedSmallInteger('oral_ml')->default(0);
            $table->unsignedSmallInteger('iv_solution_ml')->default(0);
            $table->unsignedSmallInteger('blood_ml')->default(0);
            $table->unsignedSmallInteger('plasma_ml')->default(0);
            $table->unsignedSmallInteger('sonda_ml')->default(0);
            $table->unsignedSmallInteger('other_inputs_ml')->default(0);

            // Egresos medibles (ml) — 6 columnas (resp/sudor se calcula aparte)
            $table->unsignedSmallInteger('urine_ml')->default(0);
            $table->unsignedSmallInteger('evacuation_ml')->default(0);
            $table->unsignedSmallInteger('vomit_ml')->default(0);
            $table->unsignedSmallInteger('hemorrhage_ml')->default(0);
            $table->unsignedSmallInteger('suction_ml')->default(0);
            $table->unsignedSmallInteger('canalization_ml')->default(0);

            // Pérdidas insensibles calculadas para esta toma.
            $table->unsignedSmallInteger('insensible_losses_ml')->default(0);

            // Snapshot de la fórmula usada (auditoría).
            $table->string('formula_used', 30)->nullable();
            $table->decimal('temperature_at_entry', 4, 2)->nullable();
            $table->decimal('weight_at_entry', 5, 2)->nullable();
            $table->decimal('hours_since_previous', 5, 2)->nullable();

            $table->string('observation', 500)->nullable();

            $table->foreignId('recorded_by_id')->constrained('users');

            $table->timestamps();

            $table->index(['fluid_balance_day_id', 'recorded_at'], 'fb_entries_day_recat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fluid_balance_entries');
    }
};
