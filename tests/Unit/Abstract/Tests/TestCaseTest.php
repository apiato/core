<?php

namespace Tests\Unit\Abstract\Tests;

use Apiato\Abstract\Tests\TestCase;
use Apiato\Foundation\Support\Traits\HashId;
use Apiato\Foundation\Support\Traits\Testing\Assertions;
use Apiato\Foundation\Support\Traits\Testing\RequestHelper;
use Apiato\Foundation\Support\Traits\Testing\TestingUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\UnitTestCase;

#[CoversClass(TestCase::class)]
final class TestCaseTest extends UnitTestCase
{
    public function testUsesTraits(): void
    {
        $traits = [
            TestingUser::class,
            RequestHelper::class,
            Assertions::class,
            HashId::class,
            LazilyRefreshDatabase::class,
        ];

        foreach ($traits as $trait) {
            $this->assertContains($trait, class_uses_recursive(TestCase::class));
        }
    }
}
