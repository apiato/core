<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Exceptions\Exception;
use Apiato\Core\Abstracts\Transformers\Transformer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InvalidTransformerException extends Exception
{
    protected $code = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Transformers must extended the ' . Transformer::class . ' class.';
}
