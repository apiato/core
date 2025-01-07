<?php

namespace Apiato\Abstract\Models;

use Apiato\Contracts\HasResourceKey;
use Apiato\Foundation\Support\Traits\Model\ModelTrait;
use Illuminate\Foundation\Auth\User as LaravelAuthenticatableUser;

abstract class UserModel extends LaravelAuthenticatableUser implements HasResourceKey
{
    use ModelTrait;
}
