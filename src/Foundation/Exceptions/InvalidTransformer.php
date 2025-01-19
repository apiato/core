<?php

namespace Apiato\Foundation\Exceptions;

use Apiato\Abstract\Exceptions\Exception;
use Apiato\Abstract\Transformers\Transformer;
use Symfony\Component\HttpFoundation\Response;

final class InvalidTransformer extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Transformers must extended the ' . Transformer::class . ' class.';
}
