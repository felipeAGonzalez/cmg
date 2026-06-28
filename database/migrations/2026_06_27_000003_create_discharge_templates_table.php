<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discharge_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();

            $table->text('admission_diagnosis')->nullable();
            $table->text('discharge_diagnosis')->nullable();
            $table->text('clinical_summary')->nullable();
            $table->text('physical_examination_at_discharge')->nullable();
            $table->text('plan_and_treatment_at_discharge')->nullable();
            $table->text('prognosis')->nullable();

            $table->timestamps();

            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discharge_templates');
    }
};
