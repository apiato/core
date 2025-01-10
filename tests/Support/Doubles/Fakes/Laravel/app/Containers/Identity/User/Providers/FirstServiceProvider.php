<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Providers;

use Tests\Support\Doubles\Dummies\AnotherSingletonClass;
use Tests\Support\Doubles\Dummies\AnotherSingletonInterface;
use Tests\Support\Doubles\Dummies\AnotherUselessClass;
use Tests\Support\Doubles\Dummies\AnotherUselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\ThirdServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

class FirstServiceProvider extends ServiceProvider
{
    protected array $providers = [
        ThirdServiceProvider::class,
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
}
