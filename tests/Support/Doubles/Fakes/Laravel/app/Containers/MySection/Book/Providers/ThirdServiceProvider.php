<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Tests\Support\Doubles\Dummies\SingletonClass;
use Tests\Support\Doubles\Dummies\SingletonInterface;
use Tests\Support\Doubles\Dummies\UselessClass;
use Tests\Support\Doubles\Dummies\UselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Providers\FirstServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Providers\DeferredServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

class ThirdServiceProvider extends ServiceProvider implements DeferrableProvider
{
    protected array $providers = [
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
}
