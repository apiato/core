<?php

namespace Apiato\Core\Tests;

use Apiato\Core\Tests\Concerns\PerformsAssertions;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    use PerformsAssertions;

    /**
     * Seed the DB on migrations.
     */
    protected bool $seed = true;
}
