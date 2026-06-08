<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_sign_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->dateTime('recorded_at');
            $table->string('shift');
            $table->date('shift_date');
            $table->smallInteger('heart_rate')->nullable();
            $table->smallInteger('blood_pressure_systolic')->nullable();
            $table->smallInteger('blood_pressure_diastolic')->nullable();
            $table->smallInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 2)->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['stay_id', 'recorded_at']);
            $table->index(['stay_id', 'shift_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_sign_readings');
    }
};
