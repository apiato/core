<?php

namespace Apiato\Core\Models;

use Apiato\Http\Resources\ResourceKeyAware;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;

abstract class BaseModel extends LaravelEloquentModel implements ResourceKeyAware
{
    use InteractsWithApiato;
}
