<?php

namespace Apiato\Core\Tests\Unit\Traits\TestTraits\PhpUnit;

use Apiato\Core\Tests\Unit\UnitTestCase;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestDatabaseProfilerTrait;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TestDatabaseProfilerTrait::class)]
class TestDatabaseProfilerTraitTest extends UnitTestCase
{
    use TestDatabaseProfilerTrait;

    public function testStartDatabaseQueryLog(): void
    {
        DB::expects()->enableQueryLog();

        $this->startDatabaseQueryLog();
    }

    public function testStopDatabaseQueryLog(): void
    {
        DB::expects()->disableQueryLog();

        $this->stopDatabaseQueryLog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->trait = new class {
            use TestDatabaseProfilerTrait;
        };
    }

    public function testGetDatabaseQueries(): void
    {
        $this->markTestIncomplete('To be implemented');
    }
}
