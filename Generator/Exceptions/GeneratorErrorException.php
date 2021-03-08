<?php

namespace Apiato\Core\Generator\Exceptions;

use App\Ship\Parents\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class GeneratorErrorException extends Exception
{

    public int $httpStatusCode = SymfonyResponse::HTTP_BAD_REQUEST;

    public string $message = 'Generator Error.';

}
