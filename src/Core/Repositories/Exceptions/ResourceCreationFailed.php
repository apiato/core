<?php

declare(strict_types=1);

namespace Apiato\Core\Repositories\Exceptions;

use Apiato\Core\Exceptions\HttpException as AbstractHttpException;
use Symfony\Component\HttpFoundation\Response;

final class ResourceCreationFailed extends AbstractHttpException
{
    public static function create(string $resourceName = 'Resource'): self
    {
        return new self(Response::HTTP_EXPECTATION_FAILED, $resourceName . ' creation failed.');
    }
}
