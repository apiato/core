<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BeforeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
