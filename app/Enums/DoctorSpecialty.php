<?php

namespace App\Enums;

enum DoctorSpecialty: string
{
    case GENERAL       = 'general';
    case CIRUJANO      = 'cirujano';
    case INTERNISTA    = 'internista';
    case PEDIATRA      = 'pediatra';
    case GINECOLOGO    = 'ginecologo';
    case CARDIOLOGO    = 'cardiologo';
    case TRAUMATOLOGO  = 'traumatologo';
    case ANESTESIOLOGO = 'anestesiologo';
    case OTRO          = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL       => 'Medicina General',
            self::CIRUJANO      => 'Cirujano',
            self::INTERNISTA    => 'Internista',
            self::PEDIATRA      => 'Pediatra',
            self::GINECOLOGO    => 'Ginecólogo',
            self::CARDIOLOGO    => 'Cardiólogo',
            self::TRAUMATOLOGO  => 'Traumatólogo',
            self::ANESTESIOLOGO => 'Anestesiólogo',
            self::OTRO          => 'Otro',
        };
    }

    /** Returns [value => label] array for selects. */
    public static function labels(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }

        return $result;
    }
}
