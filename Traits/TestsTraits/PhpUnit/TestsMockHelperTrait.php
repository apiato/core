<?php

namespace Apiato\Core\Traits\TestsTraits\PhpUnit;

use App;
use Mockery;
use Closure;

/**
 * Class TestsMockHelperTrait
 *
 * Tests helper for mocking objects and services.
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
trait TestsMockHelperTrait
{

    /**
     * Mocking helper
     *
     * @param $class
     *
	 * @param \Closure|null $mock
     * @return  \Mockery\MockInterface
     */
    public function mock($class, ?\Closure $mock = NULL)
    {
        $mock = Mockery::mock($class);
        App::instance($class, $mock);

        return $mock;
    }

}
