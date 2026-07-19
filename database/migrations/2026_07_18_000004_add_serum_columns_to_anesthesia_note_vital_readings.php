<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anesthesia_note_vital_readings', function (Blueprint $table) {
            $table->unsignedSmallInteger('hartmann_ml')->nullable()->after('event_marker');
            $table->unsignedSmallInteger('glucose_ml')->nullable()->after('hartmann_ml');
            $table->unsignedSmallInteger('nacl_ml')->nullable()->after('glucose_ml');
        });
    }

    public function down(): void
    {
        Schema::table('anesthesia_note_vital_readings', function (Blueprint $table) {
            $table->dropColumn(['hartmann_ml', 'glucose_ml', 'nacl_ml']);
        });
    }
};
