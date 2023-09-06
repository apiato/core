<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

class IncorrectIdException extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    protected $message = 'Incorrect value. consider using the hashed ID.';
}
