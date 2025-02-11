<?php

namespace Apiato\Abstract\Tests;

use Apiato\Support\Testing\Traits\Assertions;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    use Assertions;

    /**
     * Seed the DB on migrations.
     */
    protected bool $seed = true;
}
