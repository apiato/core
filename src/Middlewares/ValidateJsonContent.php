<?php

namespace Apiato\Core\Middlewares;

use Apiato\Core\Abstracts\Middlewares\Middleware;
use Apiato\Core\Exceptions\MissingJSONHeader;
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
