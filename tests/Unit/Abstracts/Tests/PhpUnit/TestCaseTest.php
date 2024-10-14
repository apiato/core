<?php

namespace Apiato\Core\Tests\Unit\Abstracts\Tests\PhpUnit;

use Apiato\Core\Abstracts\Tests\PhpUnit\TestCase;
use Apiato\Core\Tests\Unit\UnitTestCase;
use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\TestCaseTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestAssertionHelperTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestAuthHelperTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestDatabaseProfilerTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestRequestHelperTrait;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TestCase::class)]
final class TestCaseTest extends UnitTestCase
{
    public function testUsesTraits(): void
    {
        $traits = [
            TestCaseTrait::class,
            TestAuthHelperTrait::class,
            TestRequestHelperTrait::class,
            TestAssertionHelperTrait::class,
            HashIdTrait::class,
            LazilyRefreshDatabase::class,
            TestDatabaseProfilerTrait::class,
        ];

        foreach ($traits as $trait) {
            $this->assertContains($trait, class_uses_recursive(TestCase::class));
        }
    }
}
