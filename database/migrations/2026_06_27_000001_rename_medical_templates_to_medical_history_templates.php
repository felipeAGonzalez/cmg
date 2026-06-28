<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('medical_templates', 'medical_history_templates');
    }

    public function down(): void
    {
        Schema::rename('medical_history_templates', 'medical_templates');
    }
};
