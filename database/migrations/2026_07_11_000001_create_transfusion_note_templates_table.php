<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfusion_note_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();

            $table->text('diagnoses_and_indication')->nullable();
            $table->text('compatibility_verification')->nullable();
            $table->text('evolution_narrative')->nullable();
            $table->text('conclusion')->nullable();

            $table->timestamps();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfusion_note_templates');
    }
};
