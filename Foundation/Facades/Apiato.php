<?php

namespace Apiato\Core\Foundation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getShipFoldersNames()
 * @method static array getShipPath()
 * @method static mixed getClassObjectFromFile($filePathName)
 * @method static string getClassFullNameFromFile($filePathName)
 * @method static bool stringStartsWith(string $word, string $startsWith)
 * @method static mixed|string uncamelize(string $word, string $splitter, bool $uppercase = true)
 * @method static string buildClassFullName($containerName, $className)
 * @method static string|null getSectionNameByContainerName(string $containerName)
 * @method static array getSectionPaths()
 * @method static mixed getClassType($className)
 * @method static void verifyContainerExist($containerName)
 * @method static array getAllContainerNames()
 * @method static array getAllContainerPaths()
 * @method static array getSectionNames()
 * @method static array getSectionContainerPaths(string $sectionName)
 * @method static void verifyClassExist(string $className)
 * @method static mixed transactionalCall($class, $runMethodArguments = [], $extraMethodsToCall = [])
 * @method static mixed call($class, $runMethodArguments = [], $extraMethodsToCall = [])
 *
 * @see \Apiato\Core\Foundation\Apiato
 */
class Apiato extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Apiato';
    }
}
