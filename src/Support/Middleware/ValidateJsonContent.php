<?php

namespace Apiato\Support\Middleware;

use Apiato\Abstract\Middlewares\Middleware;
use Illuminate\Http\Request;

final class ValidateJsonContent extends Middleware
{
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->expectsJson() && config('apiato.requests.force-accept-header')) {
            throw new \RuntimeException('Missing Accept Header: application/json');
        }

        return $next($request);
    }
}
