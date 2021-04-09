<?php

namespace Apiato\Core\Foundation;

use Apiato\Core\Exceptions\AmbiguousContainerNameException;
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
    private const CONTAINERS_DIRECTORY_NAME = 'Containers';

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

    public function getSectionContainerNames(string $sectionName): array
    {
        $containerNames = [];
        foreach (File::directories($this->getSectionPath($sectionName)) as $key => $name) {
            $containerNames[] = basename($name);
        }
        return $containerNames;
    }

    private function getSectionPath(string $sectionName): string
    {
        return app_path(self::CONTAINERS_DIRECTORY_NAME . '\\' . $sectionName);
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
     * Build namespace for a class in Container.
     *
     * @param $containerName
     * @param $className
     * @param null $sectionName
     * @return  string
     */
    public function buildClassFullName($containerName, $className, $sectionName = null): string
    {
        return 'App\\' . self::CONTAINERS_DIRECTORY_NAME . '\\' . ($sectionName ?? $this->getSectionNameByContainerName($containerName))
            . '\\' . $containerName . '\\' . $this->getClassType($className) . 's\\' . $className;
    }

    public function getSectionNameByContainerName(string $containerName): ?string
    {
        foreach ($this->getSectionPaths() as $sectionPath) {
            if (is_dir($sectionPath . '\\' . $containerName)) {
                return basename($sectionPath);
            }
        }
        return null;
    }

    public function getSectionPaths(): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME));
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
     * @param null $sectionName
     * @return bool
     * @throws AmbiguousContainerNameException
     */
    public function containerExist($containerName, $sectionName = null): bool
    {
        if ($sectionName && is_dir('app\\' . self::CONTAINERS_DIRECTORY_NAME . '\\' . $sectionName . '\\' . $containerName)) {
            return true;
        }

        $containersFound = 0;
        foreach ($this->getSectionPaths() as $sectionPath) {
            if (is_dir($sectionPath . '\\' . $containerName)) {
                $containersFound++;
            }
        }

        if ($containersFound === 0) {
            return false;
        }

        if ($containersFound === 1) {
            return true;
        }

        // if more than 1 container is found throw exception
        throw new AmbiguousContainerNameException();
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

        return $sectionNames;
    }

    public function getSectionContainerPaths(string $sectionName): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME . '\\' . $sectionName));
    }
}
