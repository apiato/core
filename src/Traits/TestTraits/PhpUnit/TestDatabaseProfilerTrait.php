<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

trait TestDatabaseProfilerTrait
{
    /**
     * Start profiling database queries.
     */
    protected function startDatabaseProfiler(): void
    {
        $this->app->make('db')->enableQueryLog();
    }

    /**
     * Stop profiling database queries.
     */
    protected function stopDatabaseProfiler(): void
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
    protected function ddDatabaseQueries(): void
    {
        dd($this->getDatabaseQueries());
    }

    /**
     * Assert the number of database queries.
     */
    protected function assertDatabaseQueriesCount(int $expectedCount): void
    {
        $actualCount = count($this->getDatabaseQueries());
        $this->assertEquals($expectedCount, $actualCount, "Expected $expectedCount database queries, but got $actualCount.");
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
    protected function profileDatabaseQueriesCount(int $expectedCount, callable $callback): mixed
    {
        return $this->profileDatabaseQueries(function () use ($expectedCount, $callback) {
            $result = $callback();
            $this->assertDatabaseQueriesCount($expectedCount);

            return $result;
        });
    }
}
