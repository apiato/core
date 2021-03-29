<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UndefinedMethodException extends Exception
{
    protected $code = SymfonyResponse::HTTP_FORBIDDEN;
    protected $message = 'Undefined HTTP Verb!';
}
