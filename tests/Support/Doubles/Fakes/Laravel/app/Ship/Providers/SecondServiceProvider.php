<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

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
