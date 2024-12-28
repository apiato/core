<?php

namespace Apiato\Core\Tests\Unit\Traits;

use Apiato\Core\Exceptions\IncorrectId;
use Apiato\Core\Tests\UnitTestCase;
use Apiato\Core\Traits\HashIdTrait;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(HashIdTrait::class)]
class HashIdTraitTest extends UnitTestCase
{
    private $trait;
    private LegacyMockInterface|MockInterface $mockTrait;

    public static function hashedIdsProvider(): array
    {
        $firstId = 1;
        $secondId = 2;

        return [
            'top level value' => [
                ['id' => $firstId],
                ['id'],
                ['id' => 1],
            ],
            'top level empty string' => [
                ['id' => ''],
                ['id'],
                ['id' => ''],
            ],
            'nested value' => [
                ['data' => ['id' => $firstId]],
                ['data.id'],
                ['data' => ['id' => 1]],
            ],
            'array' => [
                ['ids' => [$firstId, $secondId]],
                ['ids.*'],
                ['ids' => [1, 2]],
            ],
            'nested array' => [
                ['nested' => ['ids' => [$firstId, $secondId]]],
                ['nested.ids.*'],
                ['nested' => ['ids' => [1, 2]]],
            ],
            'string non existent key - should return value as is' => [
                ['non_existent_key' => 'value'],
                ['id'],
                ['non_existent_key' => 'value'],
            ],
            'null top level value' => [
                ['id' => null],
                ['id'],
                ['id' => null],
            ],
            'null nested value' => [
                ['data' => ['id' => null]],
                ['data.id'],
                ['data' => ['id' => null]],
            ],
            'null array' => [
                ['ids' => [null, null]],
                ['ids.*'],
                ['ids' => [null, null]],
            ],
            'null nested array' => [
                ['nested' => ['ids' => [null, null]]],
                ['nested.ids.*'],
                ['nested' => ['ids' => [null, null]]],
            ],
        ];
    }

    public static function invalidHashedIdsProvider(): array
    {
        return [
            'top level value' => [
                ['id' => 'invalid'],
                ['id'],
            ],
            'nested value' => [
                ['data' => ['id' => 'invalid']],
                ['data.id'],
            ],
            'array' => [
                ['ids' => ['invalid', 'invalid']],
                ['ids.*'],
            ],
            'nested array' => [
                ['nested' => ['ids' => ['invalid', 'invalid']]],
                ['nested.ids.*'],
            ],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        config()->set('apiato.hash-id', true);

        $this->trait = new class {
            use HashIdTrait;

            public function publicDecodeHashedIdsBeforeValidation(array $requestData)
            {
                return $this->decodeHashedIdsBeforeValidation($requestData);
            }
        };

        $this->mockTrait = $this->mock(HashIdTrait::class)->makePartial();
    }

    public function testCanEncodeId(): void
    {
        $result = $this->trait->encode(123);
        $this->assertIsString($result);
    }

    public function testCanDecodeId(): void
    {
        $encoded = $this->trait->encode(123);
        $result = $this->trait->decode($encoded);
        $this->assertEquals(123, $result);
    }

    public function testDecodeReturnsNullForInvalidId(): void
    {
        $result = $this->trait->decode('invalid');
        $this->assertNull($result);
    }

    public function testDecodeReturnsNullForNullId(): void
    {
        $result = $this->trait->decode(null);
        $this->assertNull($result);
    }

    public function testDecodeArray(): void
    {
        $encodedArray = [
            $this->trait->encode(1),
            $this->trait->encode(2),
            $this->trait->encode(3),
        ];
        $result = $this->trait->decodeArray($encodedArray);
        $this->assertEquals([1, 2, 3], $result);
    }

    public function testSkipHashIdDecodeReturnsTrueForEmptyField(): void
    {
        $result = $this->trait->skipHashIdDecode('');
        $this->assertTrue($result);
    }

    public function testSkipHashIdDecodeReturnsFalseForNonEmptyField(): void
    {
        $result = $this->trait->skipHashIdDecode('non-empty');
        $this->assertFalse($result);
    }

    public function testCanGetHashedKeyWithDefaultField(): void
    {
        $this->markTestSkipped('this method should be moved to the model');
        $this->mockTrait->expects()
            ->method('getKeyName')
            ->andReturn('id');

        $this->mockTrait->expects()
            ->method('getAttribute')
            ->with('id')
            ->andReturn(123);

        $result = $this->trait->getHashedKey();

        $this->assertIsString($result);
    }

    public function testCanGetHashedKeyWithSpecificField(): void
    {
        $this->markTestSkipped('this method should be moved to the model');
        $this->mockTrait->expects()
            ->method('getAttribute')
            ->with('custom_field')
            ->andReturn(456);

        $result = $this->trait->getHashedKey('custom_field');

        $this->assertIsString($result);
    }

    public function testGetHashedKeyReturnsNullForNullValue(): void
    {
        $this->markTestSkipped('this method should be moved to the model');
        $this->mockTrait->expects()
            ->method('getAttribute')
            ->with('id')
            ->andReturn(null);

        $result = $this->trait->getHashedKey();

        $this->assertNull($result);
    }

    public function testSkipsDecodingIfDisabled(): void
    {
        $requestData = ['id' => $this->trait->encode(123)];
        $this->trait->decode = [];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals($requestData, $result);
    }

    #[DataProvider('hashedIdsProvider')]
    public function testCanDecodeHashedIds(array $requestData, array $decode, array $expected): void
    {
        $requestData = $this->recursiveEncode($requestData);
        $this->trait->decode = $decode;

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals($expected, $result);
    }

    private function recursiveEncode(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->recursiveEncode($value);
            }
            if (is_int($value)) {
                return $this->trait->encode($value);
            }

            return $value;
        }, $data);
    }

    public function testCanDecodeNestedAssocArray(): void
    {
        $requestData = ['nested' => ['ids' => [['first' => 1, 'second' => $this->encode(2)]]]];
        $this->trait->decode = ['nested.ids.*.second'];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals(['nested' => ['ids' => [['first' => 1, 'second' => 2]]]], $result);
    }

    public function testDecodeReturnsNullForNonHashString(): void
    {
        $result = $this->trait->decode('non_hash_string');
        $this->assertNull($result);
    }

    #[DataProvider('invalidHashedIdsProvider')]
    public function testThrowsIncorrectIdException(array $requestData, array $decode): void
    {
        $this->expectException(IncorrectId::class);

        $this->trait->decode = $decode;

        $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);
    }
}
