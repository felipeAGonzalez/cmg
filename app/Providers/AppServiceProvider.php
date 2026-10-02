<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('warehouse-integration', function (Request $request): array {
            $limits = [
                Limit::perMinute(60)->by('ip:'.$request->ip()),
            ];

            if ($request->bearerToken()) {
                $limits[] = Limit::perMinute(60)
                    ->by('token:'.hash('sha256', $request->bearerToken()));
            }

            return $limits;
        });
    }
}
