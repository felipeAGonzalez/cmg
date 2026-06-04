<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->text('diagnosis');
            $table->datetime('admission_date');
            $table->datetime('discharge_date')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'discharge_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stays');
    }
};
