<?php

namespace Apiato\Core\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait ModelTrait
{
    use HashIdTrait;
    use CanOwnTrait;
    use HashedRouteBindingTrait;
    use HasResourceKeyTrait;
    use HasFactory, FactoryLocatorTrait {
        FactoryLocatorTrait::newFactory insteadof HasFactory;
    }
}
