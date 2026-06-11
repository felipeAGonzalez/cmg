<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fluid_balance_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            // Datos clínicos
            $table->date('start_date');
            $table->text('clinical_reason')->nullable(); // motivo/condición que justifica el balance

            // Trazabilidad (patrón idéntico a MedicationOrder)
            $table->foreignId('prescribed_by_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            // Suspensión / cierre
            $table->timestamp('suspended_at')->nullable();
            $table->foreignId('suspended_by_id')->nullable()->constrained('users');
            $table->text('suspension_reason')->nullable();

            $table->timestamps();

            $table->index(['stay_id', 'suspended_at'], 'fluid_balance_orders_stay_susp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fluid_balance_orders');
    }
};
