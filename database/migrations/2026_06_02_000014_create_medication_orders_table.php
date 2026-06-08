<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            // Datos clínicos
            $table->string('medication_name', 150);
            $table->string('dose', 80); // texto libre: '500 mg', '1 tableta', '10 ml'
            $table->string('route', 30);
            $table->string('route_other', 100)->nullable();
            $table->string('frequency', 30);
            $table->string('frequency_other', 100)->nullable();
            $table->date('start_date');
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->text('indications')->nullable();

            // Trazabilidad
            $table->foreignId('prescribed_by_id')->constrained('users');
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->nullable()->constrained('users');

            // Suspensión
            $table->timestamp('suspended_at')->nullable();
            $table->foreignId('suspended_by_id')->nullable()->constrained('users');
            $table->text('suspension_reason')->nullable();

            $table->timestamps();

            $table->index(['stay_id', 'suspended_at']);
            $table->index(['prescribed_by_id', 'suspended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_orders');
    }
};
