<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Tests\Support\Doubles\Dummies\DeferredSingletonClass;
use Tests\Support\Doubles\Dummies\DeferredSingletonInterface;
use Tests\Support\Doubles\Dummies\DeferredUselessClass;
use Tests\Support\Doubles\Dummies\DeferredUselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

class DeferrableServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(DeferredUselessInterface::class, DeferredUselessClass::class);
        $this->app->singleton(DeferredSingletonInterface::class, DeferredSingletonClass::class);
    }

    public function provides(): array
    {
        return [
            DeferredUselessInterface::class,
            DeferredSingletonInterface::class,
            //            'TestDeferredProviderAlias',
        ];
    }
}
