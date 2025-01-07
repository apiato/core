<?php

namespace Apiato\Abstract\Controllers;

use Apiato\Foundation\Support\Traits\Response;

abstract class ApiController extends Controller
{
    use Response;

    /**
     * The type of this controller. This will be accessibly mirrored in the Actions.
     * Giving each Action the ability to modify it's internal business logic based on the UI type that called it.
     */
    public string $ui = 'api';
}
