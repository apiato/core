<?php

use Apiato\Core\Loaders\Apiato;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);
$apiato = Apiato::configure(basePath: $basePath);
return Application::configure(basePath: $basePath)
    ->withEvents($apiato->events())
    ->withRouting(
        then: static fn () => $apiato->registerRoutes(),
    )
    ->withMiddleware(function (Middleware $middleware) use ($apiato) {
        $middleware->api($apiato->apiMiddlewares());
    })
    ->withCommands($apiato->commands())
    ->create();