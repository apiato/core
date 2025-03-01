<?php

namespace Apiato\Core\Models;

use Apiato\Core\Models\Concerns\HandlesFactoryDiscovery;
use Apiato\Core\Models\Concerns\HandlesHashedIdRouteModelBinding;
use Apiato\Core\Models\Concerns\HasHashedId;
use Apiato\Http\Resources\HasResourceKey;

trait InteractsWithApiato
{
    use HandlesFactoryDiscovery;
    use HandlesHashedIdRouteModelBinding;
    use HasHashedId;
    use HasResourceKey;
}
