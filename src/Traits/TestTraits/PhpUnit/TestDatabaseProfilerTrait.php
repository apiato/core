<?php

namespace Apiato\Core\Traits\TestTraits\PhpUnit;

trait TestDatabaseProfilerTrait
{
    public function startDatabaseProfiler(): void
    {
        $this->app->make('db')->enableQueryLog();
    }

    public function stopDatabaseProfiler(): void
    {
        $this->app->make('db')->disableQueryLog();
    }

    public function getDatabaseQueries(): array
    {
        return $this->app->make('db')->getQueryLog();
    }

    public function dumpDatabaseQueries(): void
    {
        foreach ($this->getDatabaseQueries() as $query) {
            dump($query['query']);
        }
    }

    public function ddDatabaseQueries(): void
    {
        dd($this->getDatabaseQueries());
    }

    public function assertDatabaseQueriesCount(int $expectedCount): void
    {
        $actualCount = count($this->getDatabaseQueries());
        $this->assertEquals($expectedCount, $actualCount, "Expected $expectedCount database queries, but got $actualCount.");
    }
}
