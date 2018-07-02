<?php

namespace Apiato\Core\Exceptions;

use App\Ship\Parents\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Apiato\Core\Abstracts\Transformers\Transformer;

/**
 * Class InvalidTranformerException
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class InvalidTranformerException extends Exception
{

    public $httpStatusCode = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;

    public $message = 'Tranformer must extended of ' . Transformer::class;

}
