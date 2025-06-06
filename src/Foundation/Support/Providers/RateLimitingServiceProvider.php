<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class RateLimitingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('apiato.api.rate-limiter.enabled')) {
            RateLimiter::for(
                config('apiato.api.rate-limiter.name'),
                static fn (Request $request) => Limit::perMinutes(
                    config('apiato.api.rate-limiter.expires'),
                    config('apiato.api.rate-limiter.attempts'),
                )->by($request->user()?->id ?: $request->ip()),
            );
        }
    }
}
