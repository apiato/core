<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationException extends Exception
{
    protected $code = RESPONSE::HTTP_UNAUTHORIZED;
    protected $message = 'An Exception occurred when trying to authenticate the User.';
}
