<?php

namespace Database\Seeders;

use App\Enums\DoctorSpecialty;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'                => 'Admin',
                'last_name_one'       => 'Sistema',
                'last_name_two'       => null,
                'password'            => Hash::make('Admin1234'),
                'role'                => 'admin',
                'must_change_password' => false,
                'is_active'           => true,
            ]
        );

        $doctor = User::updateOrCreate(
            ['email' => 'doctor@example.com'],
            [
                'name'                => 'Doctor',
                'last_name_one'       => 'Ejemplo',
                'last_name_two'       => null,
                'password'            => Hash::make('Doctor1234'),
                'role'                => 'doctor',
                'must_change_password' => false,
                'is_active'           => true,
            ]
        );

        // La especialidad ahora vive en el catálogo + pivot user_specialty.
        // Se asocia al doctor de ejemplo la especialidad "Medicina General".
        $generalSpecialty = Specialty::firstOrCreate(
            ['name' => DoctorSpecialty::GENERAL->label()],
            ['is_active' => true],
        );

        $doctor->specialties()->syncWithoutDetaching([$generalSpecialty->id]);

        User::updateOrCreate(
            ['email' => 'nurse@example.com'],
            [
                'name'                => 'Enfermera',
                'last_name_one'       => 'Ejemplo',
                'last_name_two'       => null,
                'password'            => Hash::make('Nurse1234'),
                'role'                => 'nurse',
                'must_change_password' => false,
                'is_active'           => true,
            ]
        );
    }
}
