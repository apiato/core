<?php

namespace Apiato\Core\Abstracts\Tests;

use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\TestTraits\TestAssertionHelperTrait;
use Apiato\Core\Traits\TestTraits\TestAuthHelperTrait;
use Apiato\Core\Traits\TestTraits\TestRequestHelperTrait;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    use TestAuthHelperTrait;
    use TestRequestHelperTrait;
    use TestAssertionHelperTrait;
    use HashIdTrait;
    use LazilyRefreshDatabase;

    /**
     * Seed the DB on migrations.
     */
    protected bool $seed = true;
}
