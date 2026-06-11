<?php

namespace App\Services;

use App\Models\FluidBalanceEntry;
use App\Models\FluidBalanceOrder;
use App\Support\Shift;
use Carbon\Carbon;

class ShiftHoursCalculator
{
    /**
     * Calcula las "horas turno" para una nueva toma según la regla CMG:
     * - Si hay tomas anteriores en el MISMO turno: horas = diferencia entre
     *   esta toma y la anterior.
     * - Si es la primera toma del turno actual:
     *   - Primer turno del balance: horas = diferencia entre la toma y el
     *     punto de inicio (admission_date o start_date de la orden, el más
     *     reciente).
     *   - Si no es el primer turno: horas = diferencia entre la toma y el
     *     inicio del turno actual.
     */
    public static function hoursForNewEntry(FluidBalanceOrder $order, Carbon $recordedAt): float
    {
        $shiftInfo = Shift::forDateTime($recordedAt);

        // Última entry del mismo turno (shift + shift_date) dentro de esta orden.
        $previousEntry = self::orderEntries($order)
            ->where('shift', $shiftInfo['shift'])
            ->whereDate('shift_date', $shiftInfo['shift_date'])
            ->where('recorded_at', '<', $recordedAt)
            ->orderByDesc('recorded_at')
            ->first();

        if ($previousEntry) {
            $diff = $previousEntry->recorded_at->diffInMinutes($recordedAt) / 60;

            return round(max(0, $diff), 2);
        }

        // Primera toma del turno actual: ¿hay alguna toma previa del balance?
        $anyPreviousEntry = self::orderEntries($order)
            ->where('recorded_at', '<', $recordedAt)
            ->exists();

        if (! $anyPreviousEntry) {
            // Primerísima toma del balance: desde el punto de inicio.
            $admission  = $order->stay->admission_date
                ? Carbon::parse($order->stay->admission_date)
                : null;
            $orderStart = Carbon::parse($order->start_date)->startOfDay();

            $startPoint = $admission && $admission->greaterThan($orderStart)
                ? $admission
                : $orderStart;

            $diff = $startPoint->diffInMinutes($recordedAt) / 60;

            return round(max(0, $diff), 2);
        }

        // No es la primera del balance, pero sí del turno actual: desde el
        // inicio del turno.
        $diff = $shiftInfo['starts_at']->diffInMinutes($recordedAt) / 60;

        return round(max(0, $diff), 2);
    }

    /** Query base de entries de una orden (a través de sus días). */
    private static function orderEntries(FluidBalanceOrder $order)
    {
        return FluidBalanceEntry::query()
            ->whereHas('day', fn ($q) => $q->where('fluid_balance_order_id', $order->id));
    }
}
