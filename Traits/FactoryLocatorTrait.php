<?php

namespace Apiato\Core\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;

trait FactoryLocatorTrait
{
    protected static function newFactory(): Factory
    {
        $separator = '\\';
        $containersFactoriesPath = $separator . 'Data' . $separator . 'Factories' . $separator;
        $containerName = explode($separator, static::class)[2];
        $nameSpace = 'App' . $separator . 'Containers' . $separator . $containerName . $containersFactoriesPath;

        Factory::useNamespace($nameSpace);
        $className = class_basename(static::class);
        return Factory::factoryForModel($className);
    }
}
