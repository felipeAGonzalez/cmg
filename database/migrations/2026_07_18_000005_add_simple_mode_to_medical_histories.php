<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->enum('mode', ['complete', 'simple'])->default('complete')->after('id');

            // Interrogatorio
            $table->enum('simple_interrogation_type', ['directo', 'indirecto', 'diferido'])->nullable()->after('mode');

            // Antecedentes heredofamiliares
            $table->text('simple_heredo_father')->nullable();
            $table->text('simple_heredo_mother')->nullable();
            $table->text('simple_heredo_other')->nullable();

            // Antecedentes personales no patológicos
            $table->string('simple_origin', 150)->nullable();
            $table->string('simple_resident_of', 150)->nullable();
            $table->string('simple_occupation', 150)->nullable();
            $table->string('simple_education', 150)->nullable();
            $table->enum('simple_housing_type', ['propia', 'rentada', 'otro'])->nullable();
            $table->string('simple_housing_other', 150)->nullable();
            $table->enum('simple_marital_status', ['soltero', 'casado', 'otro'])->nullable();
            $table->string('simple_marital_status_other', 150)->nullable();
            $table->string('simple_diet', 255)->nullable();
            $table->string('simple_religion', 100)->nullable();
            $table->string('simple_blood_type_rh', 20)->nullable();
            $table->text('simple_hygiene')->nullable();
            $table->json('simple_non_pathological_checks')->nullable();
            $table->text('simple_non_pathological_other')->nullable();

            // Antecedentes personales patológicos
            $table->json('simple_pathological_checks')->nullable();
            $table->text('simple_pathological_other')->nullable();
            $table->text('simple_anesthetics_history')->nullable();

            // Antecedentes gineco-obstétricos
            $table->json('simple_gyneco_history')->nullable();
            $table->json('simple_gyneco_vaccines')->nullable();

            // Padecimiento actual
            $table->text('simple_current_illness')->nullable();

            // Revisión por aparatos y sistemas
            $table->json('simple_review_of_systems')->nullable();

            // Valoración de dolor
            $table->unsignedTinyInteger('simple_pain_eva_score')->nullable();
            $table->unsignedTinyInteger('simple_pain_wongbaker_score')->nullable();
            $table->enum('simple_pain_type', ['somatico', 'visceral', 'neuropatico'])->nullable();
            $table->string('simple_pain_region', 150)->nullable();
            $table->enum('simple_pain_duration', ['continuo', 'intermitente'])->nullable();
            $table->json('simple_pain_associated_signs')->nullable();
            $table->text('simple_pain_associated_factors')->nullable();

            // Exploración física
            $table->string('simple_exam_ta', 20)->nullable();
            $table->string('simple_exam_pulse', 10)->nullable();
            $table->string('simple_exam_fc', 10)->nullable();
            $table->string('simple_exam_fr', 10)->nullable();
            $table->string('simple_exam_temp', 10)->nullable();
            $table->json('simple_exam_by_system')->nullable();

            // Cierre
            $table->text('simple_lab_studies')->nullable();
            $table->text('simple_diagnosis')->nullable();
            $table->text('simple_therapeutics')->nullable();
            $table->text('simple_prognosis')->nullable();
            $table->timestamp('simple_elaboration_datetime')->nullable();
            $table->foreignId('elaborated_by_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('elaborated_by_id');
            $table->dropColumn([
                'mode',
                'simple_interrogation_type',
                'simple_heredo_father', 'simple_heredo_mother', 'simple_heredo_other',
                'simple_origin', 'simple_resident_of', 'simple_occupation', 'simple_education',
                'simple_housing_type', 'simple_housing_other',
                'simple_marital_status', 'simple_marital_status_other',
                'simple_diet', 'simple_religion', 'simple_blood_type_rh', 'simple_hygiene',
                'simple_non_pathological_checks', 'simple_non_pathological_other',
                'simple_pathological_checks', 'simple_pathological_other', 'simple_anesthetics_history',
                'simple_gyneco_history', 'simple_gyneco_vaccines',
                'simple_current_illness', 'simple_review_of_systems',
                'simple_pain_eva_score', 'simple_pain_wongbaker_score', 'simple_pain_type',
                'simple_pain_region', 'simple_pain_duration',
                'simple_pain_associated_signs', 'simple_pain_associated_factors',
                'simple_exam_ta', 'simple_exam_pulse', 'simple_exam_fc', 'simple_exam_fr', 'simple_exam_temp',
                'simple_exam_by_system',
                'simple_lab_studies', 'simple_diagnosis', 'simple_therapeutics', 'simple_prognosis',
                'simple_elaboration_datetime',
            ]);
        });
    }
};
