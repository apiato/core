<?php

namespace Tests\Unit\Abstract\Repositories\Exceptions;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;

describe(class_basename(ResourceCreationFailed::class), function (): void {
    it('can be created via factory method', function (): void {
        $exception = ResourceCreationFailed::create();
        expect($exception)->toBeInstanceOf(ResourceCreationFailed::class);
        expect($exception->getMessage())->toBe('Resource creation failed.');
        expect($exception->getStatusCode())->toBe(417);
    });
})->covers(ResourceCreationFailed::class);
