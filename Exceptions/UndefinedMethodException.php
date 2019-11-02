<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UndefinedMethodException extends Exception
{
    public $httpStatusCode = SymfonyResponse::HTTP_FORBIDDEN;

    public $message = 'Undefined HTTP Verb!';
}
