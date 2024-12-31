<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Ship\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Exceptions\HttpException as ParentHttpException;

class ResourceNotFound extends ParentHttpException
{
    public static function create(string $resourceName = 'Resource'): static
    {
        return new static(Response::HTTP_NOT_FOUND, "{$resourceName} not found.");
    }
}
