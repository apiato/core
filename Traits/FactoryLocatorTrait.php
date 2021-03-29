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
        $sectionName = $fullPathSections[1];
        $containerName = $fullPathSections[2];
        $nameSpace = 'App' . $separator . $sectionName . $separator . $containerName . $containersFactoriesPath;

        Factory::useNamespace($nameSpace);
        $className = class_basename(static::class);
        return Factory::factoryForModel($className);
    }
}
