<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class IncorrectIdException extends Exception
{
    public $httpStatusCode = SymfonyResponse::HTTP_BAD_REQUEST;

    public $message = 'ID input is incorrect.';
}
