<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

final class UnsupportedInclude extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    protected $message = 'Requested a invalid Include Parameter.';
}
