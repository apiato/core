<?php

namespace Apiato\Core\Abstracts\Models;

use Apiato\Core\Contracts\HasResourceKey;
use Apiato\Core\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;

abstract class Model extends LaravelEloquentModel implements HasResourceKey
{
    use ModelTrait;
}
