<?php

namespace Apiato\Foundation\Middleware;

use Apiato\Abstract\Middlewares\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Profiler extends Middleware
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);

        if (!config('debugbar.enabled')) {
            return $response;
        }

        if ($response instanceof JsonResponse && app()->bound('debugbar')) {
            $profilerData = ['_profiler' => app('debugbar')->getData()];

            $response->setData($response->getData(true) + $profilerData);
        }

        return $response;
    }
}