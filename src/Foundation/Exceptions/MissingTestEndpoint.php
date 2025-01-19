<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

final class MissingTestEndpoint extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Property ($this->endpoint) is missed in your test.';
}
