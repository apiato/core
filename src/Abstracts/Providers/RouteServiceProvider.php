<?php

namespace Apiato\Core\Abstracts\Providers;

use Apiato\Core\Loaders\RoutesLoaderTrait;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as LaravelRouteServiceProvider;

abstract class RouteServiceProvider extends LaravelRouteServiceProvider
{
    use RoutesLoaderTrait;

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        //        $this->routes(function () {
        //            Route::middleware('api')
        //                ->prefix('api')
        //                ->group(base_path('routes/api.php'));
        //
        //            Route::middleware('web')
        //                ->group(base_path('routes/web.php'));
        //        });
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->runRoutesAutoLoader();
    }
}
