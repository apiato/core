<?php

namespace Apiato\Core\Butlers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ContainersButler
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class ContainersButler extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ContainersButler';
    }

}

