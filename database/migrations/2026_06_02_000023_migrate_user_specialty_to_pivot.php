<?php

use App\Enums\DoctorSpecialty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migra los datos existentes de users.specialty (valor del enum
     * DoctorSpecialty) hacia el catálogo de especialidades y el pivot.
     *
     * Para que el catálogo quede legible, se siembra cada especialidad con su
     * etiqueta humana (p.ej. "Cardiólogo" en lugar de "cardiologo").
     */
    public function up(): void
    {
        // Semilla del catálogo completo a partir del enum, para que el admin
        // tenga de inicio todas las especialidades disponibles.
        foreach (DoctorSpecialty::cases() as $case) {
            DB::table('specialties')->updateOrInsert(
                ['name' => $case->label()],
                ['is_active' => true, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        if (! Schema::hasColumn('users', 'specialty')) {
            return;
        }

        $usersWithSpecialty = DB::table('users')
            ->whereNotNull('specialty')
            ->where('specialty', '!=', '')
            ->get();

        foreach ($usersWithSpecialty as $user) {
            $rawValue = trim((string) $user->specialty);
            if ($rawValue === '') {
                continue;
            }

            // El valor almacenado es el value del enum; lo convertimos a su
            // etiqueta para localizar (o crear) la especialidad del catálogo.
            $name = DoctorSpecialty::tryFrom($rawValue)?->label() ?? $rawValue;

            $specialtyId = DB::table('specialties')->where('name', $name)->value('id');

            if (! $specialtyId) {
                $specialtyId = DB::table('specialties')->insertGetId([
                    'name'       => $name,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $exists = DB::table('user_specialty')
                ->where('user_id', $user->id)
                ->where('specialty_id', $specialtyId)
                ->exists();

            if (! $exists) {
                DB::table('user_specialty')->insert([
                    'user_id'      => $user->id,
                    'specialty_id' => $specialtyId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No se restauran datos individuales.
    }
};
