<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        // root siempre pasa, independientemente del rol requerido
        if ($user->isRoot()) {
            return $next($request);
        }

        // Normaliza por si los roles llegan como 'admin,nurse' en vez de 'admin','nurse'
        $allowed = array_merge(...array_map(fn($r) => explode(',', trim($r)), $roles));

        if (! in_array($user->role, $allowed)) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
