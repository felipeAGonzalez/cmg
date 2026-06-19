<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('folio', 50)->nullable();

            $table->timestamp('evaluation_started_at');
            $table->timestamp('evaluation_ended_at')->nullable();

            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('blood_pressure_systolic')->nullable();
            $table->unsignedSmallInteger('blood_pressure_diastolic')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('oxygen_saturation')->nullable();
            $table->unsignedSmallInteger('glucose_mg_dl')->nullable();

            $table->boolean('immediate_alert_loss')->default(false);
            $table->boolean('immediate_apnea')->default(false);
            $table->boolean('immediate_no_pulse')->default(false);
            $table->boolean('immediate_intubation')->default(false);
            $table->boolean('immediate_angina')->default(false);

            $table->unsignedTinyInteger('trauma_score')->default(0);
            $table->unsignedTinyInteger('wound_score')->default(0);
            $table->unsignedTinyInteger('respiratory_difficulty_score')->default(0);
            $table->unsignedTinyInteger('cyanosis_score')->default(0);
            $table->unsignedTinyInteger('paleness_score')->default(0);
            $table->unsignedTinyInteger('hemorrhage_score')->default(0);
            $table->unsignedTinyInteger('pain_score')->default(0);
            $table->unsignedTinyInteger('intoxication_score')->default(0);
            $table->unsignedTinyInteger('seizures_score')->default(0);
            $table->unsignedTinyInteger('glasgow_score')->default(0);
            $table->unsignedTinyInteger('dehydration_score')->default(0);
            $table->unsignedTinyInteger('psychosis_score')->default(0);

            $table->unsignedTinyInteger('bp_score')->default(0);
            $table->unsignedTinyInteger('hr_score')->default(0);
            $table->unsignedTinyInteger('rr_score')->default(0);
            $table->unsignedTinyInteger('temp_score')->default(0);
            $table->unsignedTinyInteger('glucose_score')->default(0);

            $table->unsignedSmallInteger('sum_partial_a')->default(0);
            $table->unsignedSmallInteger('sum_partial_b')->default(0);
            $table->unsignedSmallInteger('total_score')->default(0);
            $table->string('color', 20)->default('blue');

            $table->foreignId('performed_by_id')->constrained('users');

            $table->timestamps();

            $table->index('patient_id');
            $table->index(['color', 'evaluation_started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage_records');
    }
};
