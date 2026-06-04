<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialty')->nullable()->after('role');
            $table->boolean('must_change_password')->default(false)->after('specialty');
            $table->boolean('is_active')->default(true)->after('must_change_password');
            $table->softDeletes();
        });

        // Migrar role 'user' → 'nurse' y cambiar default
        DB::table('users')->where('role', 'user')->update(['role' => 'nurse']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NOT NULL DEFAULT 'nurse'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['specialty', 'must_change_password', 'is_active', 'deleted_at']);
        });

        DB::table('users')->where('role', 'nurse')->update(['role' => 'user']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NOT NULL DEFAULT 'user'");
    }
};
