<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Foundation\Support\Traits\HashId;
use Apiato\Foundation\Support\Traits\Testing\AssertionTrait;
use Apiato\Foundation\Support\Traits\Testing\RequestHelperTrait;
use Apiato\Foundation\Support\Traits\Testing\TestingUserTrait;
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
            HashId::class,
            LazilyRefreshDatabase::class,
        ];

        foreach ($traits as $trait) {
            $this->assertContains($trait, class_uses_recursive(TestCase::class));
        }
    }
}
