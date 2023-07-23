<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpFoundation\Response;

#[Deprecated(
    reason: 'This exception is not used anywhere and will be removed in the next major release.'
)]
class WrongConfigurationsException extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Ops! Some Containers configurations are incorrect!';
}
