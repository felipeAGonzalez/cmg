<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anesthesia_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_surgical_note_id')->nullable()
                ->constrained('post_surgical_notes')->nullOnDelete();

            // ===== SECCIÓN 1: VALORACIÓN PREANESTÉSICA =====
            $table->enum('surgery_urgency', ['urgencia', 'electiva'])->nullable();
            $table->text('preop_diagnosis')->nullable();
            $table->text('planned_surgery')->nullable();

            $table->json('antecedents')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('previous_anesthesias')->nullable();
            $table->text('other_antecedents')->nullable();

            $table->text('current_illness')->nullable();

            $table->enum('consciousness_state', ['consciente', 'inconsciente', 'desorientado'])->nullable();
            $table->string('weight_kg', 10)->nullable();
            $table->string('height_m', 10)->nullable();
            $table->string('exam_ta', 20)->nullable();
            $table->string('exam_fc', 10)->nullable();
            $table->string('exam_fr', 10)->nullable();
            $table->string('exam_temp', 10)->nullable();
            $table->text('head_neck_exam')->nullable();
            $table->text('airway_exam')->nullable();
            $table->text('cardiopulmonary_exam')->nullable();
            $table->text('abdomen_exam')->nullable();
            $table->text('spine_exam')->nullable();
            $table->text('extremities_exam')->nullable();
            $table->text('other_exam')->nullable();

            $table->string('lab_hb', 20)->nullable();
            $table->string('lab_hto', 20)->nullable();
            $table->string('lab_tp', 20)->nullable();
            $table->string('lab_tpt', 20)->nullable();
            $table->string('lab_blood_type_rh', 20)->nullable();
            $table->string('lab_glucose', 20)->nullable();
            $table->string('lab_urea', 20)->nullable();
            $table->string('lab_creatinine', 20)->nullable();
            $table->text('other_labs')->nullable();
            $table->text('cabinet_studies')->nullable();

            $table->enum('asa_status', ['I', 'II', 'III', 'IV', 'V'])->nullable();
            $table->text('anesthetic_plan')->nullable();
            $table->text('preanesthetic_indications')->nullable();

            // ===== SECCIÓN 2: REGISTRO ANESTÉSICO =====
            $table->text('postop_diagnosis')->nullable();
            $table->text('performed_surgery')->nullable();

            $table->foreignId('or_surgeon_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('or_surgeon_other_name', 150)->nullable();
            $table->foreignId('or_assistant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('or_assistant_other_name', 150)->nullable();

            $table->string('intubation_blade', 50)->nullable();
            $table->string('intubation_cannula', 50)->nullable();
            $table->boolean('intubation_technical_difficulty')->nullable();
            $table->string('intubation_difficulty_detail', 255)->nullable();

            $table->text('ventilation_notes')->nullable();
            $table->boolean('continuous_ecg')->default(false);
            $table->boolean('pulse_oximetry')->default(false);
            $table->boolean('capnography')->default(false);

            $table->unsignedInteger('fluids_in_hartmann')->nullable();
            $table->unsignedInteger('fluids_in_glucose')->nullable();
            $table->unsignedInteger('fluids_in_nacl')->nullable();
            $table->unsignedInteger('fluids_out_diuresis')->nullable();
            $table->unsignedInteger('fluids_out_bleeding')->nullable();
            $table->unsignedInteger('fluids_out_insensible_losses')->nullable();

            $table->json('aldrete_or_exit')->nullable();

            $table->string('regional_anesthesia_type', 100)->nullable();
            $table->string('regional_needle', 50)->nullable();
            $table->string('regional_puncture_level', 50)->nullable();
            $table->boolean('regional_catheter')->nullable();
            $table->text('regional_agents_administered')->nullable();

            $table->timestamp('anesthesia_start')->nullable();
            $table->timestamp('anesthesia_end')->nullable();
            $table->timestamp('surgery_start')->nullable();
            $table->timestamp('surgery_end')->nullable();
            $table->string('anesthetic_time_total', 50)->nullable();

            $table->text('equipment_review')->nullable();
            $table->string('total_dose', 100)->nullable();
            $table->text('or_incidents')->nullable();

            // ===== SECCIÓN 3: NOTA POST ANESTÉSICA =====
            $table->text('anesthetic_technique_and_drugs')->nullable();
            $table->text('blood_fluids_administered')->nullable();
            $table->boolean('incidents_or_accidents')->nullable();
            $table->text('management_plan')->nullable();

            $table->string('ucpa_admission_ta', 20)->nullable();
            $table->string('ucpa_admission_fc', 10)->nullable();
            $table->string('ucpa_admission_fr', 10)->nullable();
            $table->string('ucpa_admission_spo2', 10)->nullable();

            $table->json('aldrete_ucpa_admission')->nullable();
            $table->json('aldrete_ucpa_discharge')->nullable();

            $table->text('evolution_and_ucpa_discharge')->nullable();

            $table->string('ucpa_discharge_ta', 20)->nullable();
            $table->string('ucpa_discharge_fc', 10)->nullable();
            $table->string('ucpa_discharge_fr', 10)->nullable();
            $table->string('ucpa_discharge_spo2', 10)->nullable();

            $table->text('postop_pain_control')->nullable();

            $table->string('discharge_ta', 20)->nullable();
            $table->string('discharge_pulse', 10)->nullable();
            $table->string('discharge_resp', 10)->nullable();
            $table->enum('discharge_consciousness', ['consciente', 'somnoliento', 'inconsciente'])->nullable();
            $table->boolean('discharge_nausea')->nullable();
            $table->boolean('discharge_vomiting')->nullable();
            $table->boolean('discharge_headache')->nullable();
            $table->string('discharge_diuresis', 100)->nullable();
            $table->string('discharge_pain', 100)->nullable();
            $table->text('discharge_evolution')->nullable();
            $table->boolean('discharge_ambulation')->nullable();

            $table->text('discharge_indications')->nullable();

            $table->foreignId('attending_doctor_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            $table->timestamps();

            $table->index('stay_id');
            $table->index('attending_doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anesthesia_notes');
    }
};
