<?php

namespace Apiato\Foundation\Support\Traits\Model;

use Apiato\Abstract\Factories\Factory;

trait FactoryDiscovery
{
    protected static function newFactory(): Factory|null
    {
        $factoryName = apiato()
            ->factoryDiscovery()
            ->resolveFactoryName(static::class);

        if (is_string($factoryName)) {
            /* @var class-string<Factory> $factoryName */
            return $factoryName::new();
        }

        return null;
    }
}
