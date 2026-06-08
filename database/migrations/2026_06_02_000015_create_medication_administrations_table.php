<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_order_id')->constrained()->cascadeOnDelete();

            $table->dateTime('administered_at');
            $table->string('shift');       // calculado desde administered_at
            $table->date('shift_date');    // calculado desde administered_at

            $table->string('actual_dose', 80);
            $table->string('status', 30);  // administered / refused / omitted
            $table->text('reason')->nullable();        // obligatorio si status != administered
            $table->text('observations')->nullable();

            $table->foreignId('recorded_by_id')->constrained('users');
            $table->timestamps();

            $table->index(['stay_id', 'administered_at'], 'med_adm_stay_admat_idx');
            $table->index(['medication_order_id', 'administered_at'], 'med_adm_order_admat_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_administrations');
    }
};
