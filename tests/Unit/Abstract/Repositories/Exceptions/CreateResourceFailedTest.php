<?php

namespace Tests\Unit\Abstract\Repositories\Exceptions;

use Workbench\App\Ship\Exceptions\CreateResourceFailed;

describe(class_basename(CreateResourceFailed::class), function (): void {
    it('can be created via factory method', function (): void {
        $exception = CreateResourceFailed::create();
        expect($exception)->toBeInstanceOf(CreateResourceFailed::class);
        expect($exception->getMessage())->toBe('Resource creation failed.');
        expect($exception->getStatusCode())->toBe(417);
    });
})->covers(CreateResourceFailed::class);
