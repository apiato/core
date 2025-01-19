<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

final class WrongEndpointFormat extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'tests ($this->endpoint) property must be formatted as "verb@url".';
}
