<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WrongConfigurationsException extends Exception
{
    protected $code = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Ops! Some Containers configurations are incorrect!';
}
