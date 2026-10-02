<?php

namespace App\Http\Controllers;

use App\Exceptions\CmgDelegatedAuthException;
use App\Models\Stay;
use App\Services\CmgDelegatedAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CmgDelegatedAccessController extends Controller
{
    public function __invoke(Request $request, Stay $stay, CmgDelegatedAuthService $service): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isNurse(), 403);

        if (! $stay->isActive()) {
            return back()->with('error', 'La hospitalización ya no se encuentra activa.');
        }

        $stay->loadMissing('room');
        $baseUrl = rtrim((string) config('cmg.url', ''), '/');

        if (! $stay->room || ! $this->isValidBaseUrl($baseUrl)) {
            Log::warning('CMG delegated access is not configured or the stay has no room.', [
                'hospital_user_id' => $user->getKey(),
                'hospitalization_id' => $stay->getKey(),
            ]);

            return back()->with('error', 'La integración con Almacén no está disponible en este momento.');
        }

        try {
            $token = $service->issue($user, $stay);
        } catch (CmgDelegatedAuthException $exception) {
            Log::warning('CMG delegated access token could not be issued.', [
                'reason' => $exception->reason,
                'hospital_user_id' => $user->getKey(),
                'hospitalization_id' => $stay->getKey(),
            ]);

            return back()->with('error', 'La integración con Almacén no está disponible en este momento.');
        }

        Log::info('CMG delegated access token issued.', [
            'hospital_user_id' => $user->getKey(),
            'hospitalization_id' => $stay->getKey(),
        ]);

        $destination = $baseUrl.'/auth/hospital/delegated?'.http_build_query(
            ['token' => $token],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );

        return redirect()->away($destination)->withHeaders(['Referrer-Policy' => 'no-referrer']);
    }

    private function isValidBaseUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
