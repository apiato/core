<?php

namespace Apiato\Foundation\Support\Traits\Model;

use Apiato\Foundation\Support\Traits\HashId;
use Apiato\Foundation\Support\Traits\HasResourceKey;
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
