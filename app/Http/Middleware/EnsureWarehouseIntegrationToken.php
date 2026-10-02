<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureWarehouseIntegrationToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredHash = config('integrations.warehouse.api_token_hash');

        if (! is_string($configuredHash) || $configuredHash === '') {
            Log::error('La autenticación de la integración con CMG Almacén no está configurada.');

            return new JsonResponse([
                'message' => 'Servicio temporalmente no disponible.',
            ], 503);
        }

        $providedToken = $request->bearerToken();

        if (! is_string($providedToken) || $providedToken === '') {
            $this->logAuthenticationFailure($request, 'missing_token');

            return $this->unauthorizedResponse();
        }

        $providedHash = hash('sha256', $providedToken);

        if (! hash_equals($configuredHash, $providedHash)) {
            $this->logAuthenticationFailure($request, 'invalid_token');

            return $this->unauthorizedResponse();
        }

        return $next($request);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'No autorizado.',
        ], 401);
    }

    private function logAuthenticationFailure(Request $request, string $reason): void
    {
        Log::warning('Falló la autenticación de la integración con CMG Almacén.', [
            'reason' => $reason,
            'ip' => $request->ip(),
        ]);
    }
}
