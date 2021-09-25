<?php

namespace Apiato\Core\Middlewares\Http;

use Apiato\Core\Exceptions\AuthenticationException;
use Exception;
use Illuminate\Auth\AuthenticationException as LaravelAuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * @throws LaravelAuthenticationException
     * @throws AuthenticationException
     */
    protected function authenticate($request, array $guards): void
    {
        try {
            parent::authenticate($request, $guards);
        } catch (Exception) {
            if ($request->expectsJson()) {
                throw new AuthenticationException();
            } else {
                $this->unauthenticated($request, $guards);
            }
        }
    }
}
