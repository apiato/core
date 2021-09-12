<?php

namespace Apiato\Core\Middlewares\Http;

use Apiato\Core\Exceptions\AuthenticationException;
use Exception;
use Illuminate\Auth\Middleware\Authenticate as LaravelAuthenticate;

class Authenticate extends LaravelAuthenticate
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
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

    protected function redirectTo($request): ?string
    {
        return route(config('apiato.web.login-page-url'));
    }
}
