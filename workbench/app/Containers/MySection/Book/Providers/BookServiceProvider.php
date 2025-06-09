<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Book\Providers;

use Illuminate\Foundation\Http\Kernel;
use Workbench\App\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Workbench\App\Ship\Parents\Providers\ServiceProvider;

class BookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // overriding the method and not calling the parents for testing purposes
    }

    public function boot(): void
    {
        app()->afterResolving(Kernel::class, static function (Kernel $kernel): void {
            $kernel->pushMiddleware(BeforeMiddleware::class);
        });
    }
}
