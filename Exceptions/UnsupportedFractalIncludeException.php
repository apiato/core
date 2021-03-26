<?php

namespace Apiato\Core\Exceptions;

use App\Ship\Parents\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnsupportedFractalIncludeException extends Exception
{

    public int $httpStatusCode = SymfonyResponse::HTTP_BAD_REQUEST;

    public string $message = 'Requested a invalid Include Parameter.';

}
