<?php

namespace Apiato\Foundation\Support;

use Illuminate\Support\Facades\File;

final readonly class PathHelper
{
    private const CONTAINERS_DIRECTORY_NAME = 'Containers';

    public static function getAppDirectoryPath(): string
    {
        return app_path();
    }

    public static function getAppDirectoryName(): string
    {
        return 'app';
    }

    public static function getSectionDirectoryPath(): string
    {
        return app_path(self::getContainersDirectoryName());
    }

    public static function getContainersDirectoryName(): string
    {
        return 'Containers';
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
        return File::directories(self::getSharedDirectoryPath());
    }

    public static function getSharedDirectoryPath(): string
    {
        return app_path(self::getSharedDirectoryName());
    }

    public static function getSharedDirectoryName(): string
    {
        return 'Ship';
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
        return self::getSharedDirectoryPath() . DIRECTORY_SEPARATOR . $subDirectory;
    }
}
