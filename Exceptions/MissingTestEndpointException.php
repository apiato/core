<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MissingTestEndpointException extends Exception
{
    protected $code = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Property ($this->endpoint) is missed in your test.';
}
