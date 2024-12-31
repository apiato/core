<?php

namespace Tests\Infrastructure\Fakes\Providers;

use Apiato\Core\Abstracts\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Tests\Infrastructure\Dummies\SingletonClass;
use Tests\Infrastructure\Dummies\SingletonInterface;
use Tests\Infrastructure\Dummies\UselessClass;
use Tests\Infrastructure\Dummies\UselessInterface;

class AggregateServiceProvider extends CoreAggregateServiceProvider implements DeferrableProvider
{
    protected $providers = [
        DeferredServiceProvider::class,
    ];

    public array $bindings = [
        UselessInterface::class => UselessClass::class,
    ];

    public array $singletons = [
        SingletonInterface::class => SingletonClass::class,
    ];

    public array $aliases = [
        'Foo' => FirstServiceProvider::class,
    ];

    public function register(): void
    {
    }

    public function boot(): void
    {
    }
}
