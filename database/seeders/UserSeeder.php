<?php

namespace Database\Seeders;

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
                'specialty'           => null,
                'must_change_password' => false,
                'is_active'           => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'doctor@example.com'],
            [
                'name'                => 'Doctor',
                'last_name_one'       => 'Ejemplo',
                'last_name_two'       => null,
                'password'            => Hash::make('Doctor1234'),
                'role'                => 'doctor',
                'specialty'           => 'general',
                'must_change_password' => false,
                'is_active'           => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'nurse@example.com'],
            [
                'name'                => 'Enfermera',
                'last_name_one'       => 'Ejemplo',
                'last_name_two'       => null,
                'password'            => Hash::make('Nurse1234'),
                'role'                => 'nurse',
                'specialty'           => null,
                'must_change_password' => false,
                'is_active'           => true,
            ]
        );
    }
}
