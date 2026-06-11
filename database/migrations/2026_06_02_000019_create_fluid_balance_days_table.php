<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fluid_balance_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fluid_balance_order_id')->constrained()->cascadeOnDelete();

            $table->unsignedSmallInteger('day_number'); // 1, 2, 3...
            $table->timestamp('start_at');              // inicio del día (puede no ser 8am)
            $table->timestamp('end_at');                // start_at + 24h
            $table->timestamp('closed_at')->nullable(); // auto al cumplir 24h o al suspender

            // Totales cacheados (se recalculan al guardar/editar/eliminar entries).
            $table->unsignedInteger('total_inputs_ml')->default(0);
            $table->unsignedInteger('total_measured_outputs_ml')->default(0);
            $table->unsignedInteger('total_insensible_losses_ml')->default(0);
            $table->integer('net_balance_ml')->default(0); // puede ser negativo

            $table->timestamps();

            $table->index(['fluid_balance_order_id', 'day_number'], 'fb_days_order_num_idx');
            $table->unique(['fluid_balance_order_id', 'day_number'], 'fb_days_order_num_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fluid_balance_days');
    }
};
