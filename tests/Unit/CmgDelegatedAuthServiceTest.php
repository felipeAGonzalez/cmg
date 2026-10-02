<?php

namespace Tests\Unit;

use App\Exceptions\CmgDelegatedAuthException;
use App\Models\Room;
use App\Models\Stay;
use App\Models\User;
use App\Services\CmgDelegatedAuthService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CmgDelegatedAuthServiceTest extends TestCase
{
    private const SECRET = 'cmg-delegated-auth-test-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'cmg.delegated_auth.secret' => self::SECRET,
            'cmg.delegated_auth.ttl' => 60,
        ]);
        Carbon::setTestNow('2026-09-03 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_token_has_two_base64url_segments_and_compatible_signature(): void
    {
        $token = $this->service()->issue($this->nurse(), $this->stay());
        $parts = explode('.', $token);

        $this->assertCount(2, $parts);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $parts[0]);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $parts[1]);
        $this->assertTrue(hash_equals(
            hash_hmac('sha256', $parts[0], self::SECRET, true),
            $this->decode($parts[1]),
        ));
    }

    public function test_payload_contains_exact_cmg_context_timing_and_audience(): void
    {
        $payload = $this->payload($this->service()->issue($this->nurse(), $this->stay()));

        $this->assertSame('91', $payload['hospital_user_id']);
        $this->assertSame('nurse', $payload['role']);
        $this->assertSame('31', $payload['patient_id']);
        $this->assertSame('41', $payload['hospitalization_id']);
        $this->assertSame('51', $payload['room_id']);
        $this->assertSame('204', $payload['room_number']);
        $this->assertSame(now()->timestamp, $payload['iat']);
        $this->assertSame(now()->timestamp + 60, $payload['exp']);
        $this->assertSame('cmg-warehouse', $payload['aud']);
        $this->assertNotEmpty($payload['nonce']);
        $this->assertArrayNotHasKey('patient_name', $payload);
    }

    public function test_each_emission_uses_a_distinct_nonce(): void
    {
        $first = $this->payload($this->service()->issue($this->nurse(), $this->stay()));
        $second = $this->payload($this->service()->issue($this->nurse(), $this->stay()));

        $this->assertNotSame($first['nonce'], $second['nonce']);
    }

    public function test_missing_secret_invalid_ttl_role_and_stay_context_fail_safely(): void
    {
        config(['cmg.delegated_auth.secret' => '']);
        $this->assertReason('missing_secret', fn () => $this->service()->issue($this->nurse(), $this->stay()));

        config(['cmg.delegated_auth.secret' => self::SECRET, 'cmg.delegated_auth.ttl' => 0]);
        $this->assertReason('invalid_ttl', fn () => $this->service()->issue($this->nurse(), $this->stay()));

        config(['cmg.delegated_auth.ttl' => 60]);
        $doctor = $this->nurse();
        $doctor->role = 'doctor';
        $this->assertReason('invalid_role', fn () => $this->service()->issue($doctor, $this->stay()));

        $discharged = $this->stay();
        $discharged->discharge_date = now();
        $this->assertReason('invalid_stay_context', fn () => $this->service()->issue($this->nurse(), $discharged));
    }

    private function service(): CmgDelegatedAuthService
    {
        return app(CmgDelegatedAuthService::class);
    }

    private function nurse(): User
    {
        $user = new User(['role' => 'nurse']);
        $user->id = 91;
        $user->exists = true;

        return $user;
    }

    private function stay(): Stay
    {
        $room = new Room(['number' => 204]);
        $room->id = 51;
        $room->exists = true;
        $stay = new Stay([
            'patient_id' => 31,
            'room_id' => 51,
            'admission_date' => now(),
        ]);
        $stay->id = 41;
        $stay->exists = true;
        $stay->setRelation('room', $room);

        return $stay;
    }

    private function payload(string $token): array
    {
        return json_decode($this->decode(explode('.', $token)[0]), true, 32, JSON_THROW_ON_ERROR);
    }

    private function decode(string $value): string
    {
        $padding = (4 - strlen($value) % 4) % 4;

        return base64_decode(strtr($value, '-_', '+/').str_repeat('=', $padding), true);
    }

    private function assertReason(string $reason, callable $callback): void
    {
        try {
            $callback();
            $this->fail('Emission should fail.');
        } catch (CmgDelegatedAuthException $exception) {
            $this->assertSame($reason, $exception->reason);
        }
    }
}
