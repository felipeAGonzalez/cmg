<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stays', function (Blueprint $table) {
            // Vincula la estancia de un recién nacido con la estancia de la madre.
            // Null = estancia normal. Con valor = nacimiento (segundo ocupante del cuarto).
            $table->foreignId('birth_parent_stay_id')
                ->nullable()
                ->after('room_id')
                ->constrained('stays')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stays', function (Blueprint $table) {
            $table->dropForeign(['birth_parent_stay_id']);
            $table->dropColumn('birth_parent_stay_id');
        });
    }
};
