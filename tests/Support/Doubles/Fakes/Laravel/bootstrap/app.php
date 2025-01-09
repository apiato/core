<?php

use Apiato\Foundation\Apiato;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);
$apiato = Apiato::configure(basePath: $basePath)
    ->create();

return Application::configure(basePath: $basePath)
    ->withEvents($apiato->events())
    ->withRouting(
        web: $apiato->webRoutes(),
        then: static fn () => $apiato->registerApiRoutes(),
    )
    ->withMiddleware(function (Middleware $middleware) use ($apiato) {
        $middleware->api($apiato->apiMiddlewares());
        //        $middleware->redirectUsersTo('login');
    })
    ->withCommands($apiato->commands())
    ->create();
