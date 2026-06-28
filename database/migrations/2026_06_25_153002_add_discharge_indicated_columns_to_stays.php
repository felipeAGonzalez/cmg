<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stays', function (Blueprint $table) {
            $table->timestamp('discharge_indicated_at')->nullable()
                ->after('discharge_reason');
            $table->foreignId('discharge_indicated_by_id')->nullable()
                ->after('discharge_indicated_at')
                ->constrained('users');

            $table->index('discharge_indicated_at');
        });
    }

    public function down(): void
    {
        Schema::table('stays', function (Blueprint $table) {
            $table->dropForeign(['discharge_indicated_by_id']);
            $table->dropIndex(['discharge_indicated_at']);
            $table->dropColumn(['discharge_indicated_at', 'discharge_indicated_by_id']);
        });
    }
};
