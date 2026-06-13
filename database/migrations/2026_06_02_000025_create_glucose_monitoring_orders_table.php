<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glucose_monitoring_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            $table->date('start_date');
            $table->string('schedule_description', 200)->nullable();
            $table->text('clinical_reason')->nullable();

            // Trazabilidad (mismo patrón que MedicationOrder y FluidBalanceOrder).
            $table->foreignId('prescribed_by_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            // Suspensión.
            $table->timestamp('suspended_at')->nullable();
            $table->foreignId('suspended_by_id')->nullable()->constrained('users');
            $table->text('suspension_reason')->nullable();

            $table->timestamps();

            $table->index(['stay_id', 'suspended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glucose_monitoring_orders');
    }
};
