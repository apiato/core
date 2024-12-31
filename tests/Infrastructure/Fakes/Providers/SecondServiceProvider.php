<?php

namespace Tests\Infrastructure\Fakes\Providers;

use Apiato\Core\Abstracts\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class SecondServiceProvider extends CoreAggregateServiceProvider implements DeferrableProvider
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
