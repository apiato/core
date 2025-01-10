<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Illuminate\Foundation\Http\Kernel;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

class BookServiceProvider extends ServiceProvider
{
    public array $bindings = [];
    public array $singletons = [];
    protected array $providers = [];
    protected array $aliases = [];

    public function register(): void
    {
        // overriding the method and not calling the parents for testing purposes
    }

    public function boot(): void
    {
        app()->afterResolving(Kernel::class, function (Kernel $kernel) {
            $kernel->pushMiddleware(BeforeMiddleware::class);
        });
    }
}
