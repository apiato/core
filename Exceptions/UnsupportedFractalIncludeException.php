<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UnsupportedFractalIncludeException extends Exception
{
    protected $code = SymfonyResponse::HTTP_BAD_REQUEST;
    protected $message = 'Requested a invalid Include Parameter.';
}
