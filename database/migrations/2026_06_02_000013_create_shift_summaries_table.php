<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->string('shift');
            $table->date('shift_date');
            $table->string('diet', 100)->nullable();
            $table->string('formula', 100)->nullable();
            $table->smallInteger('oral_liquids_ml')->nullable();
            $table->smallInteger('parenteral_liquids_ml')->nullable();
            $table->text('electrolytes_blood_elements')->nullable();
            $table->smallInteger('urine_output_ml')->nullable();
            $table->smallInteger('evacuations_count')->nullable();
            $table->smallInteger('vomit_suction_drainage_ml')->nullable();
            $table->text('lab_biological_products')->nullable();
            $table->text('reagents')->nullable();
            $table->text('studies_operations')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['stay_id', 'shift_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_summaries');
    }
};
