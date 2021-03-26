<?php

namespace Apiato\Core\Exceptions;

use Apiato\Core\Abstracts\Transformers\Transformer;
use App\Ship\Parents\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InvalidTransformerException extends Exception
{

    public int $httpStatusCode = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;

    public string $message = 'Transformers must extended the ' . Transformer::class . ' class.';

}
