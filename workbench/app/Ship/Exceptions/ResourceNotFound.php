<?php

declare(strict_types=1);

namespace Workbench\App\Ship\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Workbench\App\Ship\Parents\Exceptions\HttpException as ParentHttpException;

final class ResourceNotFound extends ParentHttpException
{
    public static function create(string $resourceName = 'Resource'): self
    {
        return new self(Response::HTTP_NOT_FOUND, $resourceName . ' not found.');
    }
}
