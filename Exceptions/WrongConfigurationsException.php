<?php

namespace Apiato\Core\Exceptions;

use App\Ship\Parents\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WrongConfigurationsException extends Exception
{

    public int $httpStatusCode = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;

    public string $message = 'Ops! Some Containers configurations are incorrect!';

}
