<?php

namespace Apiato\Core\Tests\Unit\Traits;

use Apiato\Core\Exceptions\IncorrectIdException;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Apiato\Core\Traits\HashIdTrait;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HashIdTrait::class)]
class HashIdTraitTest extends UnitTestCase
{
    private $trait;
    private LegacyMockInterface|MockInterface $mockTrait;

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

    public function testDecodeHashedIdsBeforeValidationDecodesIds(): void
    {
        $requestData = ['id' => $this->trait->encode(123)];
        $this->trait->decode = ['id'];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals(['id' => 123], $result);
    }

    public function testDecodeHashedIdsBeforeValidationSkipsDecodingIfDisabled(): void
    {
        $requestData = ['id' => $this->trait->encode(123)];
        $this->trait->decode = [];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals($requestData, $result);
    }

    public function testDecodeHashedIdsBeforeValidationDecodesNestedIds(): void
    {
        $requestData = ['data' => ['id' => $this->trait->encode(123)]];
        $this->trait->decode = ['data.id'];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals(['data' => ['id' => 123]], $result);
    }

    public function testDecodeHashedIdsBeforeValidationDecodesArrayOfIds(): void
    {
        $requestData = ['ids' => [$this->trait->encode(1), $this->trait->encode(2)]];
        $this->trait->decode = ['ids.*'];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals(['ids' => [1, 2]], $result);
    }

    public function testDecodeHashedIdsBeforeValidationThrowsIncorrectIdException(): void
    {
        $this->expectException(IncorrectIdException::class);
        $this->expectExceptionMessage('ID (id) is incorrect, consider using the hashed ID.');

        $requestData = ['id' => 'invalid'];
        $this->trait->decode = ['id'];

        $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);
    }

    public function testDecodeHashedIdsBeforeValidationReturnsDataWhenSkipHashIdDecodeIsTrue(): void
    {
        $requestData = ['id' => ''];
        $this->trait->decode = ['id'];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals($requestData, $result);
    }

    public function testDecodeReturnsNullForNonHashString(): void
    {
        $result = $this->trait->decode('string_id');
        $this->assertNull($result);
    }

    public function testDecodeHashedIdsBeforeValidationReturnsDataWhenKeyDoesNotExist(): void
    {
        $requestData = ['non_existent_key' => 'value'];
        $this->trait->decode = ['id'];

        $result = $this->trait->publicDecodeHashedIdsBeforeValidation($requestData);

        $this->assertEquals($requestData, $result);
    }
}
