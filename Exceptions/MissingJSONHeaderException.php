<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

class MissingJSONHeaderException extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    protected $message = 'Your request must contain [Accept = application/json].';
}
