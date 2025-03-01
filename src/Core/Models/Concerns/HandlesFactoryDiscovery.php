<?php

namespace Apiato\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HandlesFactoryDiscovery
{
    use HasFactory;

    protected static function newFactory()
    {
        $factoryName = apiato()->factory()
            ->resolveFactoryName(static::class);

        if (is_string($factoryName)) {
            return $factoryName::new();
        }

        return null;
    }
}
