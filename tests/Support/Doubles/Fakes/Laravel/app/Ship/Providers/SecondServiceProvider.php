<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Providers;

use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class SecondServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public array $aliases = [
        'Bar' => self::class,
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
