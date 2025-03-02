<?php

namespace Tests\Unit\Foundation\Support\Traits;

use Apiato\Core\Controllers\ApiController;
use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Containers\Identity\User\UI\API\Transformers\UserTransformer;

describe(class_basename(ApiController::class), function (): void {
    beforeEach(function (): void {
        $this->customMetadata = [
            'key' => 'value',
        ];
        $this->metadata = [
            'something' => $this->customMetadata,
        ];

        $this->sut = new class extends ApiController {};

        $this->transformer = new UserTransformer();
    });

    it('can transform data', function (): void {
        $user = User::factory()->withParent()->makeOne();
        $result = $this->sut
            ->withMeta($this->metadata)
            ->transform(
                data: $user,
                transformerName: $this->transformer,
                meta: $this->customMetadata,
            );

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('data')
            ->and($result['data'])->toHaveKey('type')
            ->and($result['data']['type'])->toBe($user->getResourceKey())
            ->and($result['data'])->not->toHaveKey('parent');
        assertMetadata($result);
    });

    it('can include requested includes', function (): void {
        $include = 'parent';

        $result = $this->sut
            ->withMeta($this->metadata)
            ->transform(
                data: User::factory()->withParent()->makeOne(),
                transformerName: $this->transformer,
                includes: [$include],
                meta: $this->customMetadata,
            );

        $this->assertArrayHasKey('parent', $result['data']);
        $this->assertNotNull($result['data']['parent']);
        assertMetadata($result);
        $this->assertContains($include, $result['meta']['include']);
    });

    it('can override resource key', function (bool|string|array|null $resourceKey, string $expected): void {
        $result = $this->sut
            ->withMeta($this->metadata)
            ->transform(
                data: User::factory()->withParent()->makeOne(),
                transformerName: $this->transformer,
                meta: $this->customMetadata,
                resourceKey: $resourceKey,
            );

        $this->assertEquals($expected, $result['data']['type']);
    })->with([
        'null' => [
            'resourceKey' => null,
            'expected' => 'User',
        ],
        'false' => [
            'resourceKey' => false,
            'expected' => 'User',
        ],
        'empty string' => [
            'resourceKey' => '',
            'expected' => 'User',
        ],
        'empty array' => [
            'resourceKey' => [],
            'expected' => 'User',
        ],
    ]);

    function assertMetadata(array $result): void
    {
        expect($result)->toHaveKey('meta')
            ->and($result['meta'])->toContainEqual(test()->metadata['something'])
            ->and($result['meta'])->toHaveKey('include')
            ->and($result['meta'])->toHaveKey('custom')
            ->and($result['meta']['custom'])->toBe(test()->customMetadata);
    }
})->covers(ApiController::class);
