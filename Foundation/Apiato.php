<?php

namespace Apiato\Core\Foundation;

use Illuminate\Support\Facades\File;

class Apiato
{
    /**
     * The Apiato version.
     */
    public const VERSION = '11.0.0';

    private const SHIP_NAME = 'ship';
    private const CONTAINERS_DIRECTORY_NAME = 'Containers';

    public function getShipFoldersNames(): array
    {
        $shipFoldersNames = [];

        foreach ($this->getShipPath() as $shipFoldersPath) {
            $shipFoldersNames[] = basename($shipFoldersPath);
        }

        return $shipFoldersNames;
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
        return app_path(self::CONTAINERS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $sectionName);
    }

    /**
     * Build and return an object of a class from its file path
     *
     * @param string $filePathName
     *
     * @return  mixed
     */
    public function getClassObjectFromFile(string $filePathName): mixed
    {
        $classString = $this->getClassFullNameFromFile($filePathName);

        return new $classString();
    }

    /**
     * Get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class"
     *
     * @param string $filePathName
     *
     * @return  string
     */
    public function getClassFullNameFromFile(string $filePathName): string
    {
        return "{$this->getClassNamespaceFromFile($filePathName)}\\{$this->getClassNameFromFile($filePathName)}";
    }

    /**
     * Get the class namespace form file path using token
     *
     * @param string $filePathName
     *
     * @return  null|string
     */
    protected function getClassNamespaceFromFile(string $filePathName): ?string
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
     * @param string $filePathName
     *
     * @return  mixed
     */
    protected function getClassNameFromFile(string $filePathName): mixed
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
     * Get the last part of a camel case string.
     * Example input = helloDearWorld | returns = World
     *
     * @param string $className
     *
     * @return  mixed
     */
    public function getClassType(string $className): mixed
    {
        $array = preg_split('/(?=[A-Z])/', $className);

        return end($array);
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

    public function getSectionPaths(): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME));
    }

    public function getSectionContainerPaths(string $sectionName): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $sectionName));
    }
}
