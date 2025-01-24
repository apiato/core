<?php

namespace Apiato\Abstract\Controllers;

use Apiato\Foundation\Support\Traits\HashId;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;

abstract class Controller extends LaravelBaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use HashId;
}
