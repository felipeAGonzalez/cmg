<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glucose_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('glucose_monitoring_order_id')->nullable()
                ->constrained()->nullOnDelete();

            $table->timestamp('recorded_at');
            $table->string('shift', 20);
            $table->date('shift_date');

            $table->unsignedSmallInteger('value_mg_dl');
            $table->string('notes', 255)->nullable();

            $table->foreignId('recorded_by_id')->constrained('users');

            $table->timestamps();

            $table->index(['stay_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glucose_readings');
    }
};
