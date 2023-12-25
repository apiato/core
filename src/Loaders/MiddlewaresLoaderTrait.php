<?php

namespace Apiato\Core\Loaders;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel;

trait MiddlewaresLoaderTrait
{
    /**
     * @throws BindingResolutionException
     */
    public function loadMiddlewares(): void
    {
        $this->registerMiddleware($this->middlewares);
        $this->registerMiddlewareGroups($this->middlewareGroups);
        $this->registerMiddlewarePriority($this->middlewarePriority);
        $this->registerMiddlewareAliases();
    }

    /**
     * @throws BindingResolutionException
     */
    public function registerMiddleware(array $middlewares = []): void
    {
        $httpKernel = $this->app->make(Kernel::class);

        foreach ($middlewares as $middleware) {
            $httpKernel->prependMiddleware($middleware);
        }
    }

    public function registerMiddlewareGroups(array $middlewareGroups = []): void
    {
        foreach ($middlewareGroups as $key => $middleware) {
            if (!is_array($middleware)) {
                $this->app['router']->pushMiddlewareToGroup($key, $middleware);
            } else {
                foreach ($middleware as $item) {
                    $this->app['router']->pushMiddlewareToGroup($key, $item);
                }
            }
        }
    }

    public function registerMiddlewarePriority(array $middlewarePriority = []): void
    {
        foreach ($middlewarePriority as $key => $middleware) {
            if (!in_array($middleware, $this->app['router']->middlewarePriority)) {
                $this->app['router']->middlewarePriority[] = $middleware;
            }
        }
    }

    public function registerMiddlewareAliases(): void
    {
        foreach ($this->getMiddlewareAliases() as $key => $value) {
            $this->app['router']->aliasMiddleware($key, $value);
        }
    }

    public function getMiddlewareAliases(): array
    {
        return array_merge($this->routeMiddleware, $this->middlewareAliases);
    }
}
