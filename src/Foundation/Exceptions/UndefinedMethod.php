<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

final class UndefinedMethod extends Exception
{
    protected $code = Response::HTTP_FORBIDDEN;
    protected $message = 'Undefined HTTP Verb!';
}
