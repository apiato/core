<?php

namespace Apiato\Core\Tests\Unit\Traits;

use Apiato\Core\Abstracts\Transformers\Transformer;
use Apiato\Core\Tests\Doubles\UserTransformer;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Apiato\Core\Traits\ResponseTrait;
use Tests\Doubles\User;
use Tests\Doubles\UserFactory;

class ResponseTraitTest extends UnitTestCase
{
    private $trait;
    private User $user;
    private Transformer $transformer;
    private array $expectedTransformedData = [
        'id' => '1',
        'name' => 'test',
    ];
    private array $customMetadata;
    private array $metadata;
    private array $includes;

    public function setUp(): void
    {
        parent::setUp();

        $this->trait = new class() {
            use ResponseTrait;
        };

        $this->user = UserFactory::new()
            ->withParent()
            ->createOne();

        $this->transformer = new class() extends Transformer {
            protected array $availableIncludes = [
                'parent',
//                'permissions',
            ];

            protected array $defaultIncludes = [
//                'permissions',
            ];

            public function transform(User $user): array
            {
                return [
                    'object' => $user->getResourceKey(),
                    'id' => $user->getHashedKey(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            }

            public function includeParent(User $user)
            {
                return $this->item($user->parent, new UserTransformer());
            }

//            public function includePermissions(User $user)
//            {
//                return $this->collection($user->permissions, new UserTransformer());
//            }
        };

        $this->expectedTransformedData = [
            'id' => $this->user->getHashedKey(),
            'name' => $this->user->name,
            'email' => $this->user->email,
            'created_at' => $this->user->created_at,
            'updated_at' => $this->user->updated_at,
        ];

        $this->includes = ['parent', 'permissions'];
        $this->customMetadata = [
            'key' => 'value',
        ];
        $this->metadata = [
            'something' => $this->customMetadata,
        ];
    }

    public function testTransform(): void
    {
        $resourceKey = null;

        $result = $this->trait
            ->withMeta($this->metadata)
            ->transform($this->user, $this->transformer, $this->includes, $this->customMetadata, $resourceKey);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(
            array_merge($this->expectedTransformedData, ['object' => $this->user->getResourceKey()]),
            $result['data'],
        );
        $this->assertMetadata($result);
    }

    // Add more test methods for each method in the ResponseTrait

    private function assertMetadata(array $result): void
    {
        $this->assertArrayHasKey('meta', $result);
        foreach ($this->metadata as $key => $value) {
            $this->assertArrayHasKey($key, $result['meta']);
            $this->assertEquals($value, $result['meta'][$key]);
        }
        $this->assertArrayHasKey('include', $result['meta']);
        $this->assertEquals($this->includes, $result['meta']['include']);
        $this->assertArrayHasKey('custom', $result['meta']);
        foreach ($this->customMetadata as $key => $value) {
            $this->assertArrayHasKey($key, $result['meta']['custom']);
            $this->assertEquals($value, $result['meta']['custom'][$key]);
        }
    }
}

/*
 *
        $this->assertArrayHasKey('something', $result['meta']);
        $this->assertArrayHasKey('key', $result['meta']['something']);
        $this->assertEquals('value', $result['meta']['something']['key']);
        $this->assertArrayHasKey('include', $result['meta']);
        $this->assertArrayHasKey('custom', $result['meta']);
        $this->assertArrayHasKey('key', $result['meta']['custom']);
        $this->assertEquals('value', $result['meta']['custom']['key']);
 */
