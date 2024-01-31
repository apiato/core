<?php

namespace Apiato\Core\Abstracts\Providers;

use Apiato\Core\Loaders\MiddlewaresLoaderTrait;
use Illuminate\Contracts\Container\BindingResolutionException;

abstract class MiddlewareServiceProvider extends MainServiceProvider
{
    use MiddlewaresLoaderTrait;

    /**
     * The application's middleware stack.
     *
     * @var array<int, class-string|string>
     */
    protected array $middlewares = [];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected array $middlewareGroups = [];

    /**
     * The application's route middleware.
     *
     * @var array<string, class-string|string>
     *
     * @deprecated
     */
    protected array $routeMiddleware = [];

    /**
     * The application's middleware aliases.
     *
     * @var array<string, class-string|string>
     */
    protected array $middlewareAliases = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected array $middlewarePriority = [];

    /**
     * Perform post-registration booting of services.
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->loadMiddlewares();
    }

    /**
     * Register anything in the container.
     */
    public function register(): void
    {
    }
}
