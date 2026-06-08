<?php

namespace App\Support;

use Carbon\Carbon;

class Shift
{
    public const MORNING = 'morning';   // 07:00 - 13:59
    public const EVENING = 'evening';   // 14:00 - 20:59
    public const NIGHT   = 'night';     // 21:00 - 06:59 (cruza medianoche)

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::MORNING => 'Matutino',
            self::EVENING => 'Vespertino',
            self::NIGHT   => 'Nocturno',
        ];
    }

    /**
     * Calcula el turno y la fecha del turno para un datetime dado.
     * El turno nocturno se asigna al día en que INICIÓ (21h).
     *
     * @return array{shift: string, shift_date: Carbon, starts_at: Carbon, ends_at: Carbon}
     */
    public static function forDateTime(Carbon $dateTime): array
    {
        $dt   = $dateTime->copy()->setTimezone(config('app.timezone'));
        $hour = $dt->hour;

        if ($hour >= 7 && $hour < 14) {
            return [
                'shift'      => self::MORNING,
                'shift_date' => $dt->copy()->startOfDay(),
                'starts_at'  => $dt->copy()->setTime(7, 0),
                'ends_at'    => $dt->copy()->setTime(13, 59, 59),
            ];
        }

        if ($hour >= 14 && $hour < 21) {
            return [
                'shift'      => self::EVENING,
                'shift_date' => $dt->copy()->startOfDay(),
                'starts_at'  => $dt->copy()->setTime(14, 0),
                'ends_at'    => $dt->copy()->setTime(20, 59, 59),
            ];
        }

        // Turno nocturno que inicia hoy: 21:00 - 06:59 del día siguiente.
        if ($hour >= 21) {
            return [
                'shift'      => self::NIGHT,
                'shift_date' => $dt->copy()->startOfDay(),
                'starts_at'  => $dt->copy()->setTime(21, 0),
                'ends_at'    => $dt->copy()->addDay()->setTime(6, 59, 59),
            ];
        }

        // hour 0-6: el turno nocturno empezó AYER a las 21:00.
        return [
            'shift'      => self::NIGHT,
            'shift_date' => $dt->copy()->subDay()->startOfDay(),
            'starts_at'  => $dt->copy()->subDay()->setTime(21, 0),
            'ends_at'    => $dt->copy()->setTime(6, 59, 59),
        ];
    }

    /**
     * @return array{shift: string, shift_date: Carbon, starts_at: Carbon, ends_at: Carbon}
     */
    public static function currentShift(): array
    {
        return self::forDateTime(now());
    }

    public static function isSameShift(Carbon $a, Carbon $b): bool
    {
        $shiftA = self::forDateTime($a);
        $shiftB = self::forDateTime($b);

        return $shiftA['shift'] === $shiftB['shift']
            && $shiftA['shift_date']->equalTo($shiftB['shift_date']);
    }

    public static function label(string $shiftCode): string
    {
        return self::labels()[$shiftCode] ?? $shiftCode;
    }

    /**
     * Rango horario legible de un turno, p.ej. "14:00 - 21:00".
     */
    public static function timeRange(string $shiftCode): string
    {
        return match ($shiftCode) {
            self::MORNING => '07:00 - 14:00',
            self::EVENING => '14:00 - 21:00',
            self::NIGHT   => '21:00 - 07:00',
            default       => '',
        };
    }
}
