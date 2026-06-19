<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('triage_records', function (Blueprint $table) {
            $table->string('disposition', 30)->default('pending')
                ->after('color');
            $table->timestamp('disposition_at')->nullable()
                ->after('disposition');
            $table->foreignId('disposition_by_id')->nullable()
                ->after('disposition_at')
                ->constrained('users');

            $table->index(['disposition', 'evaluation_started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('triage_records', function (Blueprint $table) {
            $table->dropForeign(['disposition_by_id']);
            $table->dropColumn(['disposition', 'disposition_at', 'disposition_by_id']);
        });
    }
};
