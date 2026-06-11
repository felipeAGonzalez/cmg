<?php

namespace App\Services;

use App\Models\Stay;
use Carbon\Carbon;

class InsensibleLossesCalculator
{
    public const FORMULA_CHILD_UNDER_10KG = 'child_under_10kg';
    public const FORMULA_CHILD_OVER_10KG  = 'child_over_10kg';
    public const FORMULA_ADULT_NO_FEVER   = 'adult_no_fever';
    public const FORMULA_ADULT_WITH_FEVER = 'adult_with_fever';

    public const FEVER_THRESHOLD       = 38.0;
    public const ADULT_AGE_THRESHOLD   = 12;   // ≥12 es adulto
    public const CHILD_WEIGHT_THRESHOLD = 10.0; // <10 kg es child_under

    /** Determina qué fórmula aplica según edad, peso y temperatura. */
    public static function selectFormula(int $ageYears, float $weightKg, ?float $temperatureC): string
    {
        if ($ageYears < self::ADULT_AGE_THRESHOLD) {
            return $weightKg < self::CHILD_WEIGHT_THRESHOLD
                ? self::FORMULA_CHILD_UNDER_10KG
                : self::FORMULA_CHILD_OVER_10KG;
        }

        if ($temperatureC !== null && $temperatureC >= self::FEVER_THRESHOLD) {
            return self::FORMULA_ADULT_WITH_FEVER;
        }

        return self::FORMULA_ADULT_NO_FEVER;
    }

    /**
     * Factor según temperatura (fórmulas pediátricas).
     * <38°C → 600, ≥38°C → 700, ≥39°C → 800.
     */
    public static function pediatricTempFactor(?float $temperatureC): int
    {
        if ($temperatureC === null) {
            return 600;
        }
        if ($temperatureC >= 39.0) {
            return 800;
        }
        if ($temperatureC >= 38.0) {
            return 700;
        }

        return 600;
    }

    /**
     * Calcula las pérdidas insensibles (ml) para una toma específica.
     *
     * @param string $formula      Una de las constantes FORMULA_*.
     * @param float  $weightKg     Peso del paciente.
     * @param ?float $temperatureC Última temperatura registrada.
     * @param float  $hoursElapsed Horas desde la toma anterior (o inicio).
     */
    public static function calculate(
        string $formula,
        float $weightKg,
        ?float $temperatureC,
        float $hoursElapsed
    ): int {
        if ($hoursElapsed <= 0) {
            return 0;
        }

        switch ($formula) {
            case self::FORMULA_CHILD_UNDER_10KG:
                // ((peso × 4 + 9) / 100) × factor_temp = result_24h
                $factor    = self::pediatricTempFactor($temperatureC);
                $result24h = (($weightKg * 4 + 9) / 100) * $factor;

                return (int) round($result24h / 24 * $hoursElapsed);

            case self::FORMULA_CHILD_OVER_10KG:
                // ((peso × 4 + 7) / (peso + 90)) × factor_temp = result_24h
                $factor      = self::pediatricTempFactor($temperatureC);
                $denominator = $weightKg + 90;
                if ($denominator <= 0) {
                    return 0;
                }
                $result24h = (($weightKg * 4 + 7) / $denominator) * $factor;

                return (int) round($result24h / 24 * $hoursElapsed);

            case self::FORMULA_ADULT_NO_FEVER:
                return (int) round($weightKg * 0.5 * $hoursElapsed);

            case self::FORMULA_ADULT_WITH_FEVER:
                return (int) round($weightKg * 0.7 * $hoursElapsed);

            default:
                return 0;
        }
    }

    /** Edad en años cumplidos desde la fecha de nacimiento al momento de referencia. */
    public static function ageInYears(Carbon $birthDate, Carbon $referenceDate): int
    {
        return (int) abs($birthDate->diffInYears($referenceDate));
    }

    /** Última temperatura registrada del stay hasta un momento dado (o null). */
    public static function lastTemperatureBefore(Stay $stay, Carbon $beforeMoment): ?float
    {
        $reading = $stay->vitalSignReadings()
            ->where('recorded_at', '<=', $beforeMoment)
            ->whereNotNull('temperature')
            ->orderByDesc('recorded_at')
            ->first();

        return $reading ? (float) $reading->temperature : null;
    }
}
