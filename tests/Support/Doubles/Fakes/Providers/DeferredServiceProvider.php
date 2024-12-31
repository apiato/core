<?php

namespace Tests\Support\Doubles\Fakes\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;

class DeferredServiceProvider extends CoreAggregateServiceProvider
{
    public array $aliases = [
        'Baz' => self::class,
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
