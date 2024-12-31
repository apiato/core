<?php

namespace Apiato\Foundation\Support;

use Illuminate\Support\Facades\File;

final readonly class PathHelper
{
    private const CONTAINERS_DIRECTORY_NAME = 'Containers';

    public static function getShipDirectory(): string
    {
        return app_path('Ship');
    }

    public static function getShipFolderNames(): array
    {
        $names = [];

        foreach (self::getShipSubDirectories() as $directory) {
            $names[] = basename($directory);
        }

        return $names;
    }

    public static function getShipSubDirectories(): array
    {
        return File::directories(self::getShipDirectory());
    }

    public static function getSectionContainerNames(string $sectionName): array
    {
        $names = [];
        foreach (File::directories(self::getSectionPath($sectionName)) as $name) {
            $names[] = basename($name);
        }

        return $names;
    }

    private static function getSectionPath(string $sectionName): string
    {
        return app_path(self::CONTAINERS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $sectionName);
    }

    /**
     * Get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class".
     */
    public static function getFQCNFromFile(string $filePathName): string
    {
        return self::getClassNamespaceFromFile($filePathName) . '\\' . self::getClassNameFromFile($filePathName);
    }

    // reference: https://stackoverflow.com/questions/7153000/get-class-name-from-file
    protected static function getClassNamespaceFromFile(string $filePathName): string|null
    {
        $src = file_get_contents($filePathName);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $isValidNameSpace = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && T_NAMESPACE === $token[0]) {
                // Found namespace declaration
                while (++$i < $count) {
                    if (';' === $tokens[$i]) {
                        $isValidNameSpace = true;
                        $namespace = trim($namespace);

                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }

                break;
            }
            ++$i;
        }
        if (!$isValidNameSpace) {
            return null;
        }

        return $namespace;
    }

    protected static function getClassNameFromFile(string $filePathName): mixed
    {
        $phpCode = file_get_contents($filePathName);

        $classes = [];
        $tokens = token_get_all($phpCode);
        $count = count($tokens);
        for ($i = 2; $i < $count; ++$i) {
            if (T_CLASS == $tokens[$i - 2][0]
                && T_WHITESPACE == $tokens[$i - 1][0]
                && T_STRING == $tokens[$i][0]
            ) {
                $className = $tokens[$i][1];
                $classes[] = $className;
            }
        }

        return $classes[0];
    }

    /**
     * Get the last part of a camel case string.
     * Example input = helloDearWorld | returns = World.
     */
    public static function getClassType(string $className): mixed
    {
        $array = preg_split('/(?=[A-Z])/', $className);

        return end($array);
    }

    public static function getContainerNames(): array
    {
        $names = [];

        foreach (self::getContainerPaths() as $path) {
            $names[] = basename($path);
        }

        return $names;
    }

    public static function getContainerPaths(): array
    {
        $sectionNames = self::getSectionNames();
        $containerPaths = [];
        foreach ($sectionNames as $name) {
            $sectionContainerPaths = self::getSectionContainersPaths($name);
            foreach ($sectionContainerPaths as $containerPath) {
                $containerPaths[] = $containerPath;
            }
        }

        return $containerPaths;
    }

    public static function getSectionNames(): array
    {
        $names = [];

        foreach (self::getSectionPaths() as $path) {
            $names[] = basename($path);
        }

        return $names;
    }

    public static function getSectionPaths(): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME));
    }

    public static function getSectionContainersPaths(string $sectionName): array
    {
        return File::directories(app_path(self::CONTAINERS_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $sectionName));
    }

    /**
     * @param string $subDirectory The subdirectory to append to the container path. Without leading slash.
     *
     * @return string[] array of container directories paths
     */
    public static function getContainersSubDirectories(string $subDirectory): array
    {
        return array_map(static fn (string $path) => $path . DIRECTORY_SEPARATOR . $subDirectory, self::getContainerPaths());
    }

    /**
     * @param string $subDirectory The subdirectory to append to the ship path. Without leading slash.
     */
    public static function getShipSubDirectory(string $subDirectory): string
    {
        return self::getShipDirectory() . DIRECTORY_SEPARATOR . $subDirectory;
    }
}
