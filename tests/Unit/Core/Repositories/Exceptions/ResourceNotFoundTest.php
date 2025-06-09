<?php

declare(strict_types=1);

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;

describe(class_basename(ResourceNotFound::class), function (): void {
    it('can be created via factory method', function (): void {
        $resourceNotFound = ResourceNotFound::create();
        expect($resourceNotFound)->toBeInstanceOf(ResourceNotFound::class);
        expect($resourceNotFound->getMessage())->toBe('Resource not found.');
        expect($resourceNotFound->getStatusCode())->toBe(404);
    });
})->covers(ResourceNotFound::class);
