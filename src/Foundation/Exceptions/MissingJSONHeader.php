<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

final class MissingJSONHeader extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    protected $message = 'Your request must contain [Accept = application/json] header.';
}
