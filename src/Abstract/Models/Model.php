<?php

namespace Apiato\Abstract\Models;

use Apiato\Contracts\HasResourceKey;
use Apiato\Foundation\Support\Traits\Model\ModelTrait;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;

abstract class Model extends LaravelEloquentModel implements HasResourceKey
{
    use ModelTrait;
}
