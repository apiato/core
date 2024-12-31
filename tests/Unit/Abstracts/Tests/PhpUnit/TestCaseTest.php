<?php

namespace Tests\Unit\Abstracts\Tests\PhpUnit;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Foundation\Support\Traits\HashIdTrait;
use Apiato\Foundation\Support\Traits\Testing\AssertionTrait;
use Apiato\Foundation\Support\Traits\Testing\TestingUserTrait;
use Apiato\Foundation\Support\Traits\Testing\RequestHelperTrait;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\UnitTestCase;

#[CoversClass(TestCase::class)]
final class TestCaseTest extends UnitTestCase
{
    public function testUsesTraits(): void
    {
        $traits = [
            TestingUserTrait::class,
            RequestHelperTrait::class,
            AssertionTrait::class,
            HashIdTrait::class,
            LazilyRefreshDatabase::class,
        ];

        foreach ($traits as $trait) {
            $this->assertContains($trait, class_uses_recursive(TestCase::class));
        }
    }
}
