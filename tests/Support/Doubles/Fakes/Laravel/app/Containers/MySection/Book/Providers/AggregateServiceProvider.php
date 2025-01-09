<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider as CoreAggregateServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Tests\Support\Doubles\Dummies\SingletonClass;
use Tests\Support\Doubles\Dummies\SingletonInterface;
use Tests\Support\Doubles\Dummies\UselessClass;
use Tests\Support\Doubles\Dummies\UselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Providers\FirstServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Providers\DeferredServiceProvider;

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
