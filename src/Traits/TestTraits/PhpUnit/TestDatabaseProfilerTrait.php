<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

trait TestDatabaseProfilerTrait
{
    /**
     * Start profiling database queries.
     */
    protected function startDatabaseQueryLog(): void
    {
        $this->app->make('db')->enableQueryLog();
    }

    /**
     * Stop profiling database queries.
     */
    protected function stopDatabaseQueryLog(): void
    {
        $this->app->make('db')->disableQueryLog();
    }

    /**
     * Get the database queries.
     */
    protected function getDatabaseQueries(): array
    {
        return $this->app->make('db')->getQueryLog();
    }

    /**
     * Dump the database queries.
     */
    protected function dumpDatabaseQueries(): void
    {
        foreach ($this->getDatabaseQueries() as $query) {
            dump($query['query']);
        }
    }

    /**
     * Dump and die the database queries.
     */
    protected function ddDatabaseQueries(): never
    {
        dd($this->getDatabaseQueries());
    }

    /**
     * Assert the number of database queries.
     */
    protected function assertDatabaseQueryCount(int $expectedCount): void
    {
        $actualCount = count($this->getDatabaseQueries());
        $this->assertEquals($expectedCount, $actualCount, "Expected $expectedCount database queries, but got $actualCount.");
    }

    /**
     * Assert that the database queries contain the expected query.
     */
    protected function assertDatabaseExecutedQuery(string $expectedQuery): void
    {
        $queries = $this->getDatabaseQueries();

        $found = false;
        foreach ($queries as $query) {
            if (str_contains($query['query'], $expectedQuery)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Expected query '$expectedQuery' not found in database queries.");
    }

    /**
     * Assert that the database queries contain the expected queries.
     */
    protected function assertDatabaseExecutedQueries(array $expectedQueries): void
    {
        foreach ($expectedQueries as $expectedQuery) {
            $this->assertDatabaseExecutedQuery($expectedQuery);
        }
    }

    /**
     * Wrapper to profile database queries.
     */
    protected function profileDatabaseQueries(callable $callback): mixed
    {
        $this->startDatabaseProfiler();
        $result = $callback();
        $this->stopDatabaseProfiler();

        return $result;
    }

    /**
     * Wrapper to profile database queries and assert the number of queries.
     */
    protected function profileDatabaseQueryCount(int $expectedCount, callable $callback): mixed
    {
        return $this->profileDatabaseQueries(function () use ($expectedCount, $callback) {
            $result = $callback();
            $this->assertDatabaseQueriesCount($expectedCount);

            return $result;
        });
    }

    /**
     * Wrapper to profile database queries and assert the executed query.
     */
    protected function profileDatabaseExecutedQuery(string $expectedQuery, callable $callback): mixed
    {
        return $this->profileDatabaseQueries(function () use ($expectedQuery, $callback) {
            $result = $callback();
            $this->assertDatabaseExecutedQuery($expectedQuery);

            return $result;
        });
    }
}
