<?php

namespace Apiato\Abstract\Controllers;

use Apiato\Foundation\Support\Traits\HashIdTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;

abstract class Controller extends LaravelBaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use HashIdTrait;
}
