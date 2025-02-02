<?php

namespace Apiato\Abstract\Models;

use Apiato\Abstract\Models\Concerns\HandlesHashIdRouteModelBinding;
use Apiato\Http\Resources\HasResourceKey;

trait ApiatoIntegration
{
    use HandlesHashIdRouteModelBinding;
    use HasDiscoverableFactory;
    use HasHashId;
    use HasResourceKey;
}
