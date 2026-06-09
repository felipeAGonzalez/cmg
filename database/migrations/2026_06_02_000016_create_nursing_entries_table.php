<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();

            $table->string('category', 30); // catálogo nursing_entry_categories
            $table->text('description');     // contenido principal de la entrada

            $table->timestamp('recorded_at'); // momento de la observación/registro
            $table->string('shift', 20);      // calculado desde recorded_at
            $table->date('shift_date');

            $table->foreignId('recorded_by_id')->constrained('users');

            $table->timestamps();

            $table->index(['stay_id', 'recorded_at'], 'nursing_entries_stay_recat_idx');
            $table->index(['stay_id', 'category', 'recorded_at'], 'nursing_entries_stay_cat_recat_idx');
            $table->index(['stay_id', 'shift_date', 'shift'], 'nursing_entries_stay_shift_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursing_entries');
    }
};
