<?php

namespace Apiato\Foundation\Support\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait ModelTrait
{
    use HashId;
    use CanOwn;
    use HashedRouteBinding;
    use HasResourceKey;
    use HasFactory, FactoryDiscovery {
        FactoryDiscovery::newFactory insteadof HasFactory;
    }
}
