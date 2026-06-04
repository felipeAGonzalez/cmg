<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name_one');
            $table->string('last_name_two')->nullable();
            $table->date('birth_date');
            $table->char('gender', 1);
            $table->timestamps();
            $table->softDeletes();

            // Previene duplicados por nombre completo + fecha de nacimiento.
            // Nota: MySQL permite múltiples NULLs en unique, la validación de
            // last_name_two nullable se refuerza a nivel de aplicación.
            $table->unique(['name', 'last_name_one', 'last_name_two', 'birth_date'], 'patients_full_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
