<?php

namespace Workbench\App\Ship\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Workbench\App\Ship\Parents\Exceptions\HttpException as ParentHttpException;

final class CreateResourceFailed extends ParentHttpException
{
    public static function create(string $resourceName = 'Resource'): self
    {
        return new self(Response::HTTP_EXPECTATION_FAILED, "{$resourceName} creation failed.");
    }
}
