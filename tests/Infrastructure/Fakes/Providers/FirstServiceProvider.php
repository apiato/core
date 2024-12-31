<?php

namespace Tests\Infrastructure\Fakes\Providers;

use Apiato\Core\Abstracts\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;
use Tests\Infrastructure\Dummies\AnotherSingletonClass;
use Tests\Infrastructure\Dummies\AnotherSingletonInterface;
use Tests\Infrastructure\Dummies\AnotherUselessClass;
use Tests\Infrastructure\Dummies\AnotherUselessInterface;

class FirstServiceProvider extends CoreAggregateServiceProvider
{
    protected $providers = [
        AggregateServiceProvider::class,
    ];

    public array $bindings = [
        AnotherUselessInterface::class => AnotherUselessClass::class,
    ];

    public array $singletons = [
        AnotherSingletonInterface::class => AnotherSingletonClass::class,
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
