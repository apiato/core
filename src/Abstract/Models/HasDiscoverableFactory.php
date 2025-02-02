<?php

namespace Apiato\Abstract\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HasDiscoverableFactory
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
