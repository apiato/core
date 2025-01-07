<?php

namespace Apiato\Foundation\Support\Traits\Model;

use Apiato\Abstract\Factories\Factory;
use Apiato\Foundation\Apiato;

trait FactoryDiscovery
{
    protected static function newFactory(): Factory|null
    {
        $factoryName = app()->make(Apiato::class)
            ->factoryDiscovery()
            ->resolveFactoryName(static::class);

        if ($factoryName) {
            return $factoryName::new();
        }

        return null;
    }
}
