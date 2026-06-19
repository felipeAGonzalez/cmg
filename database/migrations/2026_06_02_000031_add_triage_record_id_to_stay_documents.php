<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stay_documents', function (Blueprint $table) {
            $table->foreignId('triage_record_id')->nullable()
                ->after('document_id')
                ->constrained('triage_records')
                ->nullOnDelete();
            $table->index('triage_record_id');
        });
    }

    public function down(): void
    {
        Schema::table('stay_documents', function (Blueprint $table) {
            $table->dropForeign(['triage_record_id']);
            $table->dropColumn('triage_record_id');
        });
    }
};
