<?php

namespace Apiato\Core\Abstracts\Controllers;

use Apiato\Core\Traits\CallableTrait;
use Apiato\Core\Traits\HashIdTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;

abstract class Controller extends LaravelBaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, HashIdTrait, CallableTrait;
}
