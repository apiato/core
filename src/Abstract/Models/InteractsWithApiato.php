<?php

namespace Apiato\Abstract\Models;

use Apiato\Abstract\Models\Concerns\HandlesFactoryDiscovery;
use Apiato\Abstract\Models\Concerns\HandlesHashedIdRouteModelBinding;
use Apiato\Abstract\Models\Concerns\HasHashedId;
use Apiato\Http\Resources\HasResourceKey;

trait InteractsWithApiato
{
    use HandlesFactoryDiscovery;
    use HandlesHashedIdRouteModelBinding;
    use HasHashedId;
    use HasResourceKey;
}
