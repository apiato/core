<?php

declare(strict_types=1);

use Apiato\Foundation\Apiato;
use Apiato\Support\HashidsManagerDecorator;
use Safe\Exceptions\FilesystemException;

use function Safe\glob;

if (!function_exists('apiato')) {
    /**
     * Get the Apiato instance.
     */
    function apiato(): Apiato
    {
        return app(Apiato::class);
    }
}

if (!function_exists('shared_path')) {
    /**
     * Get the path to the application's shared directory.
     */
    function shared_path(string $path = ''): string
    {
        return Apiato::instance()->sharedPath($path);
    }
}

if (!function_exists('hashids')) {
    /**
     * Get the Hashids instance.
     */
    function hashids(): HashidsManagerDecorator
    {
        return app('hashids');
    }
}

if (!function_exists('recursiveGlob')) {
    /**
     * Recursively find files matching a pattern.
     *
     * @return string[]
     *
     * @throws FilesystemException
     */
    function recursiveGlob(string $pattern, int $flags = 0): array
    {
        /** @var string[] $topLevelFiles */
        $topLevelFiles = glob($pattern, $flags);
        /** @var string[] $dirs */
        $dirs = glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

        $allFiles = $topLevelFiles;
        foreach ($dirs as $dir) {
            $allFiles = [...$allFiles, ...recursiveGlob($dir . '/' . basename($pattern), $flags)];
        }

        return $allFiles;
    }
}
