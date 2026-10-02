<?php

namespace Tests\Unit;

use App\Models\MedicationAdministration;
use App\Models\MedicationOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class MedicationOrderScheduleTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_every_eight_hours_is_anchored_to_first_effective_administration(): void
    {
        Carbon::setTestNow('2026-10-02 13:00:00');

        $order = $this->orderWithAdministrations('every_8h', [
            ['2026-10-02 07:00:00', MedicationAdministration::STATUS_ADMINISTERED],
        ]);

        $this->assertSame(['07:00', '15:00', '23:00'], $order->dailyScheduleTimes());
        $this->assertSame('2026-10-02 15:00', $order->nextScheduledAdministrationAt()?->format('Y-m-d H:i'));
    }

    public function test_a_late_later_dose_does_not_move_the_original_schedule(): void
    {
        Carbon::setTestNow('2026-10-02 16:00:00');

        $order = $this->orderWithAdministrations('every_8h', [
            ['2026-10-02 07:00:00', MedicationAdministration::STATUS_ADMINISTERED],
            ['2026-10-02 15:20:00', MedicationAdministration::STATUS_ADMINISTERED],
        ]);

        $this->assertSame(['07:00', '15:00', '23:00'], $order->dailyScheduleTimes());
        $this->assertSame('2026-10-02 23:00', $order->nextScheduledAdministrationAt()?->format('Y-m-d H:i'));
    }

    public function test_refused_or_omitted_records_do_not_create_the_schedule_anchor(): void
    {
        $order = $this->orderWithAdministrations('every_8h', [
            ['2026-10-02 07:00:00', MedicationAdministration::STATUS_REFUSED],
            ['2026-10-02 15:00:00', MedicationAdministration::STATUS_OMITTED],
        ]);

        $this->assertSame([], $order->dailyScheduleTimes());
        $this->assertNull($order->nextScheduledAdministrationAt());
    }

    public function test_prn_and_free_text_frequencies_do_not_generate_an_automatic_schedule(): void
    {
        foreach (['prn', 'single_dose', 'other'] as $frequency) {
            $order = $this->orderWithAdministrations($frequency, [
                ['2026-10-02 07:00:00', MedicationAdministration::STATUS_ADMINISTERED],
            ]);

            $this->assertNull($order->frequencyIntervalHours());
            $this->assertSame([], $order->dailyScheduleTimes());
            $this->assertNull($order->nextScheduledAdministrationAt());
        }
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $administrations
     */
    private function orderWithAdministrations(string $frequency, array $administrations): MedicationOrder
    {
        $order = new MedicationOrder([
            'frequency' => $frequency,
            'start_date' => '2026-10-02',
        ]);

        $order->setRelation('administrations', new Collection(array_map(
            fn (array $administration) => new MedicationAdministration([
                'administered_at' => $administration[0],
                'status' => $administration[1],
            ]),
            $administrations
        )));

        return $order;
    }
}
