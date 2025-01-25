<?php

use Apiato\Foundation\Apiato;
use Apiato\Support\Middleware\ProcessETag;
use Apiato\Support\Middleware\ValidateJsonContent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);
$apiato = Apiato::configure(basePath: $basePath)
    ->withEvents(
        ...glob($basePath . '/app/Containers/*/Author/Listeners', GLOB_ONLYDIR),
    )->create();

return Application::configure(basePath: $basePath)
    ->withProviders($apiato->providers())
    ->withEvents($apiato->events())
    ->withRouting(
        web: $apiato->webRoutes(),
        then: static fn () => $apiato->registerApiRoutes(),
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api([
            ValidateJsonContent::class,
            ProcessETag::class,
        ]);
    })
    ->withCommands($apiato->commands())
    ->create();
