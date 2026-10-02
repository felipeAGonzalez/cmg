<?php

namespace App\Services;

use App\Exceptions\CmgDelegatedAuthException;
use App\Models\Stay;
use App\Models\User;
use JsonException;

class CmgDelegatedAuthService
{
    public function issue(User $user, Stay $stay): string
    {
        $secret = (string) config('cmg.delegated_auth.secret', '');
        $ttl = (int) config('cmg.delegated_auth.ttl', 60);

        if ($secret === '') {
            throw new CmgDelegatedAuthException('missing_secret');
        }

        if ($ttl <= 0) {
            throw new CmgDelegatedAuthException('invalid_ttl');
        }

        if (! $user->isNurse()) {
            throw new CmgDelegatedAuthException('invalid_role');
        }

        if (! $stay->isActive() || ! $stay->room) {
            throw new CmgDelegatedAuthException('invalid_stay_context');
        }

        $issuedAt = now()->timestamp;
        $payload = [
            'hospital_user_id' => (string) $user->getKey(),
            'role' => 'nurse',
            'patient_id' => (string) $stay->patient_id,
            'hospitalization_id' => (string) $stay->getKey(),
            'room_id' => (string) $stay->room_id,
            'room_number' => (string) $stay->room->number,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $ttl,
            'nonce' => bin2hex(random_bytes(16)),
            'aud' => 'cmg-warehouse',
        ];

        try {
            $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        } catch (JsonException $exception) {
            throw new CmgDelegatedAuthException('encoding_failed');
        }

        $signature = hash_hmac('sha256', $encodedPayload, $secret, true);

        return $encodedPayload.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
