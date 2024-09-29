<?php

namespace Apiato\Core\Tests\Unit\Traits;

use Apiato\Core\Tests\Unit\UnitTestCase;
use Apiato\Core\Traits\HashIdTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(HashIdTrait::class)]
class HashIdTraitTest extends UnitTestCase
{
    private $trait;

    public function setUp(): void
    {
        parent::setUp();

        $this->trait = new class {
            use HashIdTrait;
        };
    }

    public function testProcessFieldShouldNotWrapANullInAnArray(): void
    {
        $data = null;
        $keysTodo = ['*'];
        $currentFieldName = null;
        $reflection = new ReflectionClass($this->trait);
        $method = $reflection->getMethod('processField');

        $result = $method->invoke($this->trait, $data, $keysTodo, $currentFieldName);

        $this->assertNull($result);
    }
}
