<?php

namespace Apiato\Core\Abstracts\Models;

use Apiato\Core\Traits\HashIdTrait;
use Apiato\Core\Traits\HasResourceKeyTrait;
use Illuminate\Database\Eloquent\Model as LaravelEloquentModel;

/**
 * Class Model.
 *
 * @author  Mahmoud Zalt <mahmoud@zalt.me>
 */
abstract class Model extends LaravelEloquentModel
{

    use HashIdTrait;
    use HasResourceKeyTrait;

}
