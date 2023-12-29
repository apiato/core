<?php

namespace Apiato\Core\Abstracts\Models;

use Apiato\Core\Traits\ModelTrait;
use Illuminate\Foundation\Auth\User as LaravelAuthenticatableUser;

abstract class UserModel extends LaravelAuthenticatableUser
{
    use ModelTrait;
}
