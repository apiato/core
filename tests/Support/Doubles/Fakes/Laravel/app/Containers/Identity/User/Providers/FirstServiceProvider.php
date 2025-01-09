<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;
use Tests\Support\Doubles\Dummies\AnotherSingletonClass;
use Tests\Support\Doubles\Dummies\AnotherSingletonInterface;
use Tests\Support\Doubles\Dummies\AnotherUselessClass;
use Tests\Support\Doubles\Dummies\AnotherUselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\AggregateServiceProvider;

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
