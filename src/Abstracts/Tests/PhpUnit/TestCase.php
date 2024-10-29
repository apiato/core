<?php

namespace Apiato\Core\Abstracts\Tests\PhpUnit;

use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestAssertionHelperTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestAuthHelperTrait;
use Apiato\Core\Traits\TestTraits\PhpUnit\TestRequestHelperTrait;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Illuminate\Support\Facades\Artisan;

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

    protected function afterRefreshingDatabase(): void
    {
        Artisan::call('passport:install');
    }
}
