<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('discharge_notes');

        Schema::create('discharge_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            $table->text('admission_diagnosis')->nullable();
            $table->text('discharge_diagnosis')->nullable();
            $table->text('clinical_summary')->nullable();
            $table->text('physical_examination_at_discharge')->nullable();
            $table->text('plan_and_treatment_at_discharge')->nullable();
            $table->text('prognosis')->nullable();

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
        Schema::dropIfExists('discharge_notes');
    }
};
