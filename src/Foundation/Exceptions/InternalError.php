<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

final class InternalError extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Something went wrong!';
}
