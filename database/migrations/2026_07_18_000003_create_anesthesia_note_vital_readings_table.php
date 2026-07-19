<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anesthesia_note_vital_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anesthesia_note_id')->constrained()->cascadeOnDelete();
            $table->time('reading_time');
            $table->unsignedSmallInteger('ta_sys')->nullable();
            $table->unsignedSmallInteger('ta_dia')->nullable();
            $table->unsignedSmallInteger('fc')->nullable();
            $table->unsignedSmallInteger('fr')->nullable();
            $table->decimal('temp', 4, 1)->nullable();
            $table->unsignedTinyInteger('spo2')->nullable();
            $table->string('event_marker', 50)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('anesthesia_note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anesthesia_note_vital_readings');
    }
};
