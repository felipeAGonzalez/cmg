<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_surgical_note_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();

            $table->text('preop_diagnosis')->nullable();
            $table->text('postop_diagnosis')->nullable();
            $table->text('planned_surgery')->nullable();
            $table->text('performed_surgery')->nullable();
            $table->text('complications')->nullable();
            $table->text('bleeding')->nullable();
            $table->text('patient_status_at_exit')->nullable();
            $table->text('prognosis')->nullable();
            $table->text('surgical_technique')->nullable();

            $table->timestamps();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_surgical_note_templates');
    }
};
