<?php

namespace Apiato\Core\Repositories\Exceptions;

use Apiato\Core\Exceptions\HttpException as AbstractHttpException;
use Symfony\Component\HttpFoundation\Response;

final class ResourceNotFound extends AbstractHttpException
{
    public static function create(string $resourceName = 'Resource'): self
    {
        return new self(Response::HTTP_NOT_FOUND, "{$resourceName} not found.");
    }
}
