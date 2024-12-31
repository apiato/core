<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Middlewares;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BeforeMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        return $next($request);
    }
}
