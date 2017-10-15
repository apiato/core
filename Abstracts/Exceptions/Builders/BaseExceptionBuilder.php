<?php

namespace Apiato\Core\Abstracts\Exceptions\Builders;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class BaseExceptionBuilder
{
    public static function make(Exception $e)
    {
        return new JsonResponse([
        ]);
    }
}
