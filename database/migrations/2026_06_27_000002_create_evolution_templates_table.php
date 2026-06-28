<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evolution_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 500)->nullable();

            $table->text('antecedents')->nullable();
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('analysis')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('prognosis')->nullable();
            $table->text('plan')->nullable();

            $table->timestamps();

            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evolution_templates');
    }
};
