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

    public function testGetDatabaseQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertEquals($queries, $this->getDatabaseQueries());
    }

    public function testAssertDatabaseQueryCount(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseQueryCount(2);
    }

    public function testAssertDatabaseQueryCountWithDifferentCount(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage('Expected 3 database queries, but got 2.');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseQueryCount(3);
    }

    public function testAssertDatabaseQueryCountWithEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseQueryCount(0);
    }

    public function testAssertDatabaseExecutedQuery(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseExecutedQuery('query1');
    }

    public function testAssertDatabaseExecutedQueryWithDifferentQuery(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query3' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQuery('query3');
    }

    public function testAssertDatabaseExecutedQueryWithEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQuery('query1');
    }

    public function testAssertDatabaseExecutedQueryWithEmptyQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query '' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQuery('');
    }

    public function testAssertDatabaseExecutedQueryWithDifferentQueriesAndEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQuery('query1');
    }

    public function testAssertDatabaseExecutedQueryWithEmptyQueriesAndDatabaseQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseExecutedQuery('');
    }

    public function testAssertDatabaseExecutedQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2'], ['query' => 'query3']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseExecutedQueries(['query1', 'query2']);
    }

    public function testAssertDatabaseExecutedQueriesWithDifferentQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query3' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQueries(['query1', 'query3']);
    }

    public function testAssertDatabaseExecutedQueriesWithDifferentQueries2(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query4' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQueries(['query4', 'query3']);
    }

    public function testAssertDatabaseExecutedQueriesWithEmptyQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseExecutedQueries([]);
    }

    public function testAssertDatabaseExecutedQueriesWithEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQueries(['query1']);
    }

    public function testAssertDatabaseExecutedQueriesWithEmptyQueriesAndDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->assertDatabaseExecutedQueries([]);
    }

    public function testAssertDatabaseExecutedQueriesWithDifferentQueriesAndEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->assertDatabaseExecutedQueries(['query1']);
    }

    public function testProfileDatabaseQueries(): void
    {
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();

        $this->profileDatabaseQueries(function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseQueriesRunsClosure(): void
    {
        $closureRan = false;
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();

        $this->profileDatabaseQueries(function () use (&$closureRan) {
            $closureRan = true;
        });

        $this->assertTrue($closureRan);
    }

    public function testProfileDatabaseQueriesWithException(): void
    {
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();

        $this->expectException(\Exception::class);
        $this->profileDatabaseQueries(function () {
            throw new \Exception();
        });
    }

    public function testProfileDatabaseQueryCount(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseQueryCount(2, function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseQueryCountWithDifferentCount(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage('Expected 3 database queries, but got 2.');
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseQueryCount(3, function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseQueryCountRunsClosure(): void
    {
        $closureRan = false;
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn([]);

        $this->profileDatabaseQueryCount(0, function () use (&$closureRan) {
            $closureRan = true;
        });

        $this->assertTrue($closureRan);
    }

    public function testProfileDatabaseQueryCountWithException(): void
    {
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();

        $this->expectException(\Exception::class);
        $this->profileDatabaseQueryCount(0, function () {
            throw new \Exception();
        });
    }

    public function testProfileDatabaseExecutedQuery(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQuery('query1', function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueryWithDifferentQuery(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query3' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseExecutedQuery('query3', function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueryWithEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseExecutedQuery('query1', function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueryWithEmptyQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQuery('', function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueryWithDifferentQueriesAndEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseExecutedQuery('query1', function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueryWithEmptyQueriesAndDatabaseQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQuery('', function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueryRunsClosure(): void
    {
        $closureRan = false;
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQuery('query1', function () use (&$closureRan) {
            $closureRan = true;
        });

        $this->assertTrue($closureRan);
    }

    public function testProfileDatabaseExecutedQueryWithException(): void
    {
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();

        $this->expectException(\Exception::class);
        $this->profileDatabaseExecutedQuery('query1', function () {
            throw new \Exception();
        });
    }

    public function testProfileDatabaseExecutedQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQueries(['query1', 'query2'], function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueriesWithDifferentQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query3' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseExecutedQueries(['query1', 'query3'], function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueriesWithDifferentQueries2(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query4' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseExecutedQueries(['query4', 'query3'], function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueriesWithEmptyQueries(): void
    {
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQueries([], function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueriesWithEmptyDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->expectExceptionMessage("Expected query 'query1' not found in database queries.");
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);
        $this->profileDatabaseExecutedQueries(['query1'], function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueriesWithEmptyQueriesAndDatabaseQueries(): void
    {
        $queries = [];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQueries([], function () {
            // Do nothing
        });
    }

    public function testProfileDatabaseExecutedQueriesRunsClosure(): void
    {
        $closureRan = false;
        $queries = [['query' => 'query1'], ['query' => 'query2']];
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();
        DB::expects()->getQueryLog()->andReturn($queries);

        $this->profileDatabaseExecutedQueries(['query1'], function () use (&$closureRan) {
            $closureRan = true;
        });

        $this->assertTrue($closureRan);
    }

    public function testProfileDatabaseExecutedQueriesWithException(): void
    {
        DB::expects()->enableQueryLog();
        DB::expects()->disableQueryLog();

        $this->expectException(\Exception::class);
        $this->profileDatabaseExecutedQueries(['query1'], function () {
            throw new \Exception();
        });
    }
}
