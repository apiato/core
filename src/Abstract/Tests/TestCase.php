<?php

namespace Apiato\Abstract\Tests;

use Apiato\Support\Testing\Traits\Assertions;
use Apiato\Support\Testing\Traits\RequestHelper;
use Apiato\Support\Testing\Traits\TestingUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    use TestingUser;
    use RequestHelper;
    use Assertions;
    use LazilyRefreshDatabase;

    /**
     * Seed the DB on migrations.
     */
    protected bool $seed = true;
}
