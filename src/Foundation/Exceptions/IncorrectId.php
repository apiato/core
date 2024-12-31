<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response;

class IncorrectId extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;
    protected $message = 'Incorrect value. consider using the hashed ID.';
}
