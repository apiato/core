<?php

namespace Apiato\Core\Abstracts\Tests\PhpUnit;

use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\TestCaseTrait;
use Apiato\Core\Traits\TestsTraits\PhpUnit\TestsAuthHelperTrait;
use Apiato\Core\Traits\TestsTraits\PhpUnit\TestsMockHelperTrait;
use Apiato\Core\Traits\TestsTraits\PhpUnit\TestsRequestHelperTrait;
use Apiato\Core\Traits\TestsTraits\PhpUnit\TestsResponseHelperTrait;
use Apiato\Core\Traits\TestsTraits\PhpUnit\TestsUploadHelperTrait;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;

/**
 * Class TestCase
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
abstract class TestCase extends LaravelTestCase
{

    use TestCaseTrait,
        TestsRequestHelperTrait,
        TestsResponseHelperTrait,
        TestsMockHelperTrait,
        TestsAuthHelperTrait,
        TestsUploadHelperTrait,
        HashIdTrait;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Setup the test environment, before each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // migrate the database
        $this->migrateDatabase();

        // seed the database
        $this->seed();

        // Install Passport Client for Testing
        $this->setupPassportOAuth2();
    }

    /**
     * Reset the test environment, after each test.
     */
    public function tearDown()
    {
        $this->artisan('migrate:reset');
    }

}
