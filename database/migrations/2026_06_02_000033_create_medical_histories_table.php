<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            $table->text('family_history')->nullable();
            $table->text('non_pathological_history')->nullable();
            $table->text('pathological_history')->nullable();
            $table->text('current_illness')->nullable();
            $table->text('general_symptoms')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('diagnostic_aids')->nullable();
            $table->text('main_diagnoses')->nullable();
            $table->text('comorbidities')->nullable();
            $table->text('clinical_plan')->nullable();
            $table->text('signature_block')->nullable();

            $table->foreignId('attending_doctor_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            $table->timestamps();

            $table->unique('stay_id');
            $table->index('attending_doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};
