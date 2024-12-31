<?php

namespace Tests\Infrastructure\Fakes\Providers;

use Apiato\Core\Abstracts\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;

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
