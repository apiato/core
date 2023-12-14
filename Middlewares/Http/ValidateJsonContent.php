<?php

namespace Apiato\Core\Middlewares\Http;

use Apiato\Core\Abstracts\Middlewares\Middleware;
use Apiato\Core\Exceptions\MissingJSONHeaderException;
use Illuminate\Http\Request;

class ValidateJsonContent extends Middleware
{
    /**
     * @throws MissingJSONHeaderException
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->expectsJson() && config('apiato.requests.force-accept-header')) {
            throw new MissingJSONHeaderException();
        }

        return $next($request);
    }
}
