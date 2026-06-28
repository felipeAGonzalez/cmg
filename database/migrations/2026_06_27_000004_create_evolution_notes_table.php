<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evolution_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->timestamp('note_datetime');

            $table->text('antecedents')->nullable();
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('analysis')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('prognosis')->nullable();
            $table->text('plan')->nullable();

            $table->timestamp('medications_from')->nullable();
            $table->timestamp('medications_to')->nullable();

            $table->foreignId('attending_doctor_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['stay_id', 'note_datetime']);
            $table->index('attending_doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evolution_notes');
    }
};
