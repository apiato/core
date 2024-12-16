<?php

namespace Apiato\Core\Tests\Unit\Traits;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Apiato\Core\Tests\Infrastructure\Doubles\User;
use Apiato\Core\Tests\Infrastructure\Doubles\UserFactory;
use Apiato\Core\Tests\Infrastructure\Doubles\UserTransformer;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Apiato\Core\Traits\ResponseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ResponseTrait::class)]
class ResponseTraitTest extends UnitTestCase
{
    private $trait;
    private User $user;
    private Transformer $transformer;
    private array $customMetadata;
    private array $metadata;

    public function setUp(): void
    {
        parent::setUp();

        $this->trait = new class {
            use ResponseTrait;
        };

        $this->user = UserFactory::new()->withParent()->createOne();
        $this->transformer = new UserTransformer();
        $this->customMetadata = [
            'key' => 'value',
        ];
        $this->metadata = [
            'something' => $this->customMetadata,
        ];
    }

    public function testTransform(): void
    {
        $result = $this->trait
            ->withMeta($this->metadata)
            ->transform(
                data: $this->user,
                transformerName: $this->transformer,
                meta: $this->customMetadata,
            );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('object', $result['data']);
        $this->assertEquals($this->user->getResourceKey(), $result['data']['object']);
        $this->assertArrayNotHasKey('parent', $result['data']);
        $this->assertMetadata($result);
    }

    public function testCanInclude(): void
    {
        $include = 'parent';

        $result = $this->trait
            ->withMeta($this->metadata)
            ->transform(
                data: $this->user,
                transformerName: $this->transformer,
                includes: [$include],
                meta: $this->customMetadata,
            );

        $this->assertArrayHasKey('parent', $result['data']);
        $this->assertNotNull($result['data']['parent']);
        $this->assertMetadata($result);
        $this->assertContains($include, $result['meta']['include']);
    }

    public static function resourceKeyProvider(): array
    {
        return [
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
            //            'empty object' => [
            //                'resourceKey' => new \stdClass(),
            //                'expected' => 'User',
            //            ],
            //            'override resource key' => [
            //                'resource key' => 'override-key',
            //                'expected' => 'override-key',
            //            ],
        ];
    }

    #[DataProvider('resourceKeyProvider')]
    public function testCanOverrideResourceKey($resourceKey, $expected): void
    {
        $result = $this->trait
            ->withMeta($this->metadata)
            ->transform(
                data: $this->user,
                transformerName: $this->transformer,
                meta: $this->customMetadata,
                resourceKey: $resourceKey,
            );

        $this->assertEquals($expected, $result['data']['object']);
    }

    private function assertMetadata(array $result): void
    {
        $this->assertArrayHasKey('meta', $result);
        foreach ($this->metadata as $key => $value) {
            $this->assertArrayHasKey($key, $result['meta']);
            $this->assertEquals($value, $result['meta'][$key]);
        }
        $this->assertArrayHasKey('include', $result['meta']);
        $this->assertArrayHasKey('custom', $result['meta']);
        foreach ($this->customMetadata as $key => $value) {
            $this->assertArrayHasKey($key, $result['meta']['custom']);
            $this->assertEquals($value, $result['meta']['custom'][$key]);
        }
    }
}
