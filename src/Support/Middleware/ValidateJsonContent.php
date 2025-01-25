<?php

namespace Apiato\Support\Middleware;

use Apiato\Abstract\Middlewares\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ValidateJsonContent extends Middleware
{
    /**
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if (!$request->expectsJson() && config('apiato.requests.force-accept-header')) {
            throw new \RuntimeException('Missing Accept Header: application/json');
        }

        return $next($request);
    }
}
