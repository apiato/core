<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Illuminate\Foundation\Http\Kernel;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Providers\ServiceProvider;

class BookServiceProvider extends ServiceProvider
{
    public array $bindings = [];
    public array $singletons = [];
    protected $providers = [];
    protected array $aliases = [];

    public function boot(): void
    {
        app()->afterResolving(Kernel::class, function (Kernel $kernel) {
            $kernel->pushMiddleware(BeforeMiddleware::class);
        });
    }
}
