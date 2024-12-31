<?php

namespace Apiato\Abstract\Tests;

use Apiato\Foundation\Support\Traits\HashIdTrait;
use Apiato\Foundation\Support\Traits\Testing\AssertionTrait;
use Apiato\Foundation\Support\Traits\Testing\RequestHelperTrait;
use Apiato\Foundation\Support\Traits\Testing\TestingUserTrait;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    use TestingUserTrait;
    use RequestHelperTrait;
    use AssertionTrait;
    use HashIdTrait;
    use LazilyRefreshDatabase;

    /**
     * Seed the DB on migrations.
     */
    protected bool $seed = true;
}
