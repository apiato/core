<?php

declare(strict_types=1);

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;

describe(class_basename(ResourceCreationFailed::class), function (): void {
    it('can be created via factory method', function (): void {
        $resourceCreationFailed = ResourceCreationFailed::create();
        expect($resourceCreationFailed)->toBeInstanceOf(ResourceCreationFailed::class);
        expect($resourceCreationFailed->getMessage())->toBe('Resource creation failed.');
        expect($resourceCreationFailed->getStatusCode())->toBe(417);
    });
})->covers(ResourceCreationFailed::class);
