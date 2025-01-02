<?php

namespace Apiato\Foundation\Middleware;

use Apiato\Abstract\Middlewares\Middleware;
use Apiato\Foundation\Exceptions\MissingJSONHeader;
use Illuminate\Http\Request;

class ValidateJsonContent extends Middleware
{
    /**
     * @throws MissingJSONHeader
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->expectsJson() && config('apiato.requests.force-accept-header')) {
            throw new MissingJSONHeader();
        }

        return $next($request);
    }
}
