<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anesthesia_note_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();

            $table->text('current_illness')->nullable();
            $table->text('anesthetic_plan')->nullable();
            $table->text('anesthetic_technique_and_drugs')->nullable();
            $table->text('evolution_and_ucpa_discharge')->nullable();
            $table->text('postop_pain_control')->nullable();

            $table->timestamps();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anesthesia_note_templates');
    }
};
