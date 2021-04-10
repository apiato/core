<?php

namespace Apiato\Core\Traits;

use Illuminate\Database\Eloquent\Factories\Factory;

trait FactoryLocatorTrait
{
    protected static function newFactory(): Factory
    {
        $separator = '\\';
        $containersFactoriesPath = $separator . 'Data' . $separator . 'Factories' . $separator;
        $fullPathSections = explode($separator, static::class);
        $sectionName = $fullPathSections[2];
        $containerName = $fullPathSections[3];
        $nameSpace = 'App' . $separator . 'Containers' . $separator . $sectionName . $separator . $containerName . $containersFactoriesPath;

        Factory::useNamespace($nameSpace);
        $className = class_basename(static::class);
        return Factory::factoryForModel($className);
    }
}
