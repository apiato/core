<?php

namespace Apiato\Abstract\Tests;

use Apiato\Foundation\Support\Traits\HashId;
use Apiato\Foundation\Support\Traits\Testing\Assertions;
use Apiato\Foundation\Support\Traits\Testing\RequestHelper;
use Apiato\Foundation\Support\Traits\Testing\TestingUser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    use TestingUser;
    use RequestHelper;
    use Assertions;
    use HashId;
    use LazilyRefreshDatabase;

    /**
     * Seed the DB on migrations.
     */
    protected bool $seed = true;
}
