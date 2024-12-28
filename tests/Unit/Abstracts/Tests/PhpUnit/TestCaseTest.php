<?php

namespace Apiato\Core\Tests\Unit\Abstracts\Tests\PhpUnit;

use Apiato\Core\Abstracts\Tests\TestCase;
use Apiato\Core\Tests\UnitTestCase;
use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\TestTraits\TestAssertionHelperTrait;
use Apiato\Core\Traits\TestTraits\TestAuthHelperTrait;
use Apiato\Core\Traits\TestTraits\TestRequestHelperTrait;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TestCase::class)]
final class TestCaseTest extends UnitTestCase
{
    public function testUsesTraits(): void
    {
        $traits = [
            TestAuthHelperTrait::class,
            TestRequestHelperTrait::class,
            TestAssertionHelperTrait::class,
            HashIdTrait::class,
            LazilyRefreshDatabase::class,
        ];

        foreach ($traits as $trait) {
            $this->assertContains($trait, class_uses_recursive(TestCase::class));
        }
    }
}
