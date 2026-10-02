<?php

use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureWarehouseIntegrationToken;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'prevent.back' => PreventBackHistory::class,
            'role' => EnsureUserHasRole::class,
            'user.active' => EnsureUserIsActive::class,
            'password.changed' => EnsurePasswordIsChanged::class,
            'warehouse.integration' => EnsureWarehouseIntegrationToken::class,
        ]);

        $middleware->appendToGroup('web', [
            PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
