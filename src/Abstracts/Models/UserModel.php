<?php

namespace Apiato\Core\Abstracts\Models;

use Apiato\Core\Contracts\HasResourceKey;
use Apiato\Core\Traits\ModelTrait;
use Illuminate\Foundation\Auth\User as LaravelAuthenticatableUser;

abstract class UserModel extends LaravelAuthenticatableUser implements HasResourceKey
{
    use ModelTrait;
}
