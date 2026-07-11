<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfusion_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transfusion_checklist_id')->nullable()
                ->constrained('transfusion_checklists')->nullOnDelete();

            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('end_datetime')->nullable();

            $table->text('diagnoses_and_indication')->nullable();
            $table->text('compatibility_verification')->nullable();
            $table->text('evolution_narrative')->nullable();
            $table->text('conclusion')->nullable();

            $table->string('pre_ta', 20)->nullable();
            $table->string('pre_fc', 10)->nullable();
            $table->string('pre_fr', 10)->nullable();
            $table->string('pre_temp', 10)->nullable();
            $table->string('pre_spo2', 10)->nullable();

            $table->string('post_ta', 20)->nullable();
            $table->string('post_fc', 10)->nullable();
            $table->string('post_fr', 10)->nullable();
            $table->string('post_temp', 10)->nullable();
            $table->string('post_spo2', 10)->nullable();

            $table->foreignId('attending_doctor_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['stay_id', 'start_datetime']);
            $table->index('attending_doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfusion_notes');
    }
};
