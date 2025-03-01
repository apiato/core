<?php

namespace Tests\Unit\Abstract\Repositories\Exceptions;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;

describe(class_basename(ResourceNotFound::class), function (): void {
    it('can be created via factory method', function (): void {
        $exception = ResourceNotFound::create();
        expect($exception)->toBeInstanceOf(ResourceNotFound::class);
        expect($exception->getMessage())->toBe('Resource not found.');
        expect($exception->getStatusCode())->toBe(404);
    });
})->covers(ResourceNotFound::class);
