<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_surgical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            $table->date('surgery_date')->nullable();
            $table->time('surgery_time')->nullable();
            $table->enum('surgery_type', ['urgencia', 'programada'])->nullable();

            $table->text('preop_diagnosis')->nullable();
            $table->text('postop_diagnosis')->nullable();
            $table->text('planned_surgery')->nullable();
            $table->text('performed_surgery')->nullable();

            $table->string('surgical_time', 50)->nullable();
            $table->text('complications')->nullable();
            $table->text('bleeding')->nullable();

            $table->enum('textile_count', ['completo', 'incompleto'])->nullable();
            $table->string('textile_count_detail', 255)->nullable();

            $table->string('ischemia_time', 50)->nullable();

            $table->text('patient_status_at_exit')->nullable();
            $table->text('prognosis')->nullable();

            $table->foreignId('surgeon_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('surgeon_other_name', 150)->nullable();

            $table->foreignId('assistant_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assistant_other_name', 150)->nullable();

            $table->foreignId('anesthesiologist_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('anesthesiologist_other_name', 150)->nullable();

            $table->text('surgical_technique')->nullable();

            $table->foreignId('attending_doctor_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['stay_id', 'surgery_date']);
            $table->index('attending_doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_surgical_notes');
    }
};
