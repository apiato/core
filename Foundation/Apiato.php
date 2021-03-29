<?php

namespace Apiato\Core\Foundation;

use Apiato\Core\Exceptions\ClassDoesNotExistException;
use Apiato\Core\Exceptions\MissingContainerException;
use Apiato\Core\Traits\CallableTrait;
use Illuminate\Support\Facades\File;

class Apiato
{
    use CallableTrait;

    /**
     * The Apiato version.
     */
    public const VERSION = '10.0.0';

    private const SHIP_NAME = 'ship';

    public function getShipFoldersNames(): array
    {
        $portFoldersNames = [];

        foreach ($this->getShipPath() as $portFoldersPath) {
            $portFoldersNames[] = basename($portFoldersPath);
        }

        return $portFoldersNames;
    }

    public function getShipPath(): array
    {
        return File::directories(app_path(self::SHIP_NAME));
    }

    /**
     * Build and return an object of a class from its file path
     *
     * @param $filePathName
     *
     * @return  mixed
     */
    public function getClassObjectFromFile($filePathName)
    {
        $classString = $this->getClassFullNameFromFile($filePathName);

        return new $classString;
    }

    /**
     * Get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class"
     *
     * @param $filePathName
     *
     * @return  string
     */
    public function getClassFullNameFromFile($filePathName): string
    {
        return $this->getClassNamespaceFromFile($filePathName) . '\\' . $this->getClassNameFromFile($filePathName);
    }

    /**
     * Get the class namespace form file path using token
     *
     * @param $filePathName
     *
     * @return  null|string
     */
    protected function getClassNamespaceFromFile($filePathName): ?string
    {
        $src = file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        }

        return $namespace;
    }

    /**
     * Get the class name from file path using token
     *
     * @param $filePathName
     *
     * @return  mixed
     */
    protected function getClassNameFromFile($filePathName)
    {
        $php_code = file_get_contents($filePathName);

        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes[0];
    }

    /**
     * Check if a word starts with another word
     * @param string $word
     * @param string $startsWith
     * @return bool
     */
    public function stringStartsWith(string $word, string $startsWith): bool
    {
        return (substr($word, 0, strlen($startsWith)) === $startsWith);
    }

    /**
     * @param        $word
     * @param string $splitter
     * @param bool $uppercase
     *
     * @return  mixed|string
     */
    public function uncamelize($word, $splitter = " ", $uppercase = true)
    {
        $word = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0',
            preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $word));

        return $uppercase ? ucwords($word) : $word;
    }

    /**
     * Build namespace for a class in Container.
     *
     * @param $containerName
     * @param $className
     *
     * @return  string
     */
    public function buildClassFullName($containerName, $className): string
    {
        return 'App\\' . $this->getSectionNameByContainerName($containerName) . '\\' . $containerName . '\\' . $this->getClassType($className) . 's\\' . $className;
    }

    public function getSectionNameByContainerName(string $containerName): ?string
    {
        foreach ($this->getSectionPaths() as $sectionPath) {
            if (is_dir($sectionPath . '/' . $containerName)) {
                return basename($sectionPath);
            }
        }
    }

    public function getSectionPaths(): array
    {
        $paths = File::directories(app_path());

        // remove ship from paths
        foreach ($paths as $key => $path) {
            if ($this->isShip(basename($path))) {
                array_splice($paths, $key, 1);
            }
        }
        return $paths;
    }

    protected function isShip(string $name): bool
    {
        return strtolower($name) === self::SHIP_NAME;
    }

    /**
     * Get the last part of a camel case string.
     * Example input = helloDearWorld | returns = World
     *
     * @param $className
     *
     * @return  mixed
     */
    public function getClassType($className)
    {
        $array = preg_split('/(?=[A-Z])/', $className);

        return end($array);
    }

    /**
     * @param $containerName
     *
     * @throws MissingContainerException
     */
    public function verifyContainerExist($containerName): void
    {
        $containerExist = false;
        // if it exist in at least one section then we count it as "exist"
        foreach ($this->getSectionPaths() as $sectionPath) {
            if (is_dir($sectionPath . '/' . $containerName)) {
                $containerExist = true;
            }
        }

        if (!$containerExist) {
            throw new MissingContainerException("Container ($containerName) is not installed.");
        }
    }

    public function getAllContainerNames(): array
    {
        $containersNames = [];

        foreach ($this->getAllContainerPaths() as $containersPath) {
            $containersNames[] = basename($containersPath);
        }

        return $containersNames;
    }

    public function getAllContainerPaths(): array
    {
        $sectionNames = $this->getSectionNames();
        $containerPaths = [];
        foreach ($sectionNames as $name) {
            $sectionContainerPaths = $this->getSectionContainerPaths($name);
            foreach ($sectionContainerPaths as $containerPath) {
                $containerPaths[] = $containerPath;
            }
        }
        return $containerPaths;
    }

    public function getSectionNames(): array
    {
        $sectionNames = [];

        foreach ($this->getSectionPaths() as $sectionPath) {
            $sectionNames[] = basename($sectionPath);
        }

        // remove ship from names
        foreach ($sectionNames as $key => $sectionName) {
            if ($this->isShip($sectionName)) {
                array_splice($sectionNames, $key, 1);
            }
        }

        return $sectionNames;
    }

    public function getSectionContainerPaths(string $sectionName): array
    {
        return File::directories(app_path($sectionName));
    }

    /**
     * @param $className
     *
     * @throws ClassDoesNotExistException
     */
    public function verifyClassExist(string $className): void
    {
        if (!class_exists($className)) {
            throw new ClassDoesNotExistException("Class ($className) is not installed.");
        }
    }
}
