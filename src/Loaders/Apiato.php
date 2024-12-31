<?php

namespace Apiato\Core\Loaders;

use Apiato\Core\Middlewares\ProcessETag;
use Apiato\Core\Middlewares\Profiler;
use Apiato\Core\Middlewares\ValidateJsonContent;
use Apiato\Core\Utilities\PathHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\SplFileInfo;

final class Apiato
{
    private static string $basePath;
    private static array $providerPaths = [];
    private static array $configPaths = [];
    private static array $listenerPaths = [];
    private static array $commandPaths = [];

    public static function configure(string $basePath): ApplicationBuilder
    {
        self::$basePath = $basePath;

        return (new ApplicationBuilder())
            ->withProviders(
                $basePath . '/app/Ship/Providers',
                ...glob($basePath . '/app/Containers/*/*/Providers', GLOB_ONLYDIR | GLOB_NOSORT),
            )
            ->withConfigs(
                $basePath . '/app/Ship/Configs',
                ...glob($basePath . '/app/Containers/*/*/Configs', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withEvents(
                $basePath . '/app/Ship/Listeners',
                ...glob($basePath . '/app/Containers/*/*/Listeners', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withCommands(
                $basePath . '/app/Ship/Commands',
                ...glob($basePath . '/app/Containers/*/*/UI/Console', GLOB_ONLYDIR | GLOB_NOSORT),
            );
    }

    public static function loadProvidersFrom(string ...$path): void
    {
        self::$providerPaths = $path;
    }

    public static function loadConfigsFrom(string ...$path): void
    {
        self::$configPaths = $path;
    }

    public static function loadEventsFrom(string ...$path): void
    {
        self::$listenerPaths = $path;
    }

    public static function loadCommandsFrom(string ...$path): void
    {
        self::$commandPaths = $path;
    }

    public static function providerPaths(): array
    {
        return self::$providerPaths;
    }

    public static function configPaths(): array
    {
        return self::$configPaths;
    }

    public static function getListeners(): array
    {
        return self::$listenerPaths;
    }

    public static function getApiMiddlewares(): array
    {
        return [
            ValidateJsonContent::class,
            ProcessETag::class,
            Profiler::class,
        ];
    }

    public static function getCommands(): array
    {
        return self::$commandPaths;
    }

    public function registerRoutes(): void
    {
        $allContainerPaths = PathHelper::getContainerPaths();

        foreach ($allContainerPaths as $containerPath) {
            $this->loadContainerApiRoutes($containerPath);
            $this->loadContainerWebRoutes($containerPath);
        }
    }

    private function loadContainerApiRoutes(string $containerPath): void
    {
        $apiRoutesPath = $this->getRoutePathsForUI($containerPath, 'API');

        if (File::isDirectory($apiRoutesPath)) {
            $files = $this->getFilesSortedByName($apiRoutesPath);
            foreach ($files as $file) {
                $this->loadApiRoute($file);
            }
        }
    }

    private function getRoutePathsForUI(string $containerPath, string $ui): string
    {
        return $containerPath . DIRECTORY_SEPARATOR . 'UI' . DIRECTORY_SEPARATOR . $ui . DIRECTORY_SEPARATOR . 'Routes';
    }

    /**
     * @return array|SplFileInfo[]
     */
    private function getFilesSortedByName(string $apiRoutesPath): array
    {
        $files = File::allFiles($apiRoutesPath);

        return Arr::sort($files, static fn ($file) => $file->getFilename());
    }

    private function loadApiRoute(SplFileInfo $file): void
    {
        $routeGroupArray = $this->getApiRouteGroup($file);

        Route::group($routeGroupArray, static function () use ($file): void {
            require $file->getPathname();
        });
    }

    public function getApiRouteGroup(SplFileInfo|string $endpointFileOrPrefixString): array
    {
        return [
            'middleware' => $this->getMiddlewares(),
            'domain' => config('apiato.api.url'),
            // If $endpointFileOrPrefixString is a string, use that string as prefix
            // else, if it is a file then get the version name from the file name, and use it as prefix
            'prefix' => is_string($endpointFileOrPrefixString) ? $endpointFileOrPrefixString : $this->getApiVersionPrefix($endpointFileOrPrefixString),
        ];
    }

    private function getMiddlewares(): array
    {
        $middlewares = ['api'];
        if (config('apiato.api.rate-limiter.enabled')) {
            $middlewares[] = 'throttle:' . config('apiato.api.rate-limiter.name');
        }

        return $middlewares;
    }

    private function getApiVersionPrefix(SplFileInfo $file): string
    {
        return config('apiato.api.prefix') . (config('apiato.api.enable_version_prefix') ? $this->getRouteFileVersionFromFileName($file) : '');
    }

    private function getRouteFileVersionFromFileName(SplFileInfo $file): string|bool
    {
        $fileNameWithoutExtension = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $fileNameWithoutExtensionExploded = explode('.', $fileNameWithoutExtension);

        end($fileNameWithoutExtensionExploded);

        return prev($fileNameWithoutExtensionExploded);
    }

    private function loadContainerWebRoutes($containerPath): void
    {
        $webRoutesPath = $this->getRoutePathsForUI($containerPath, 'WEB');

        if (File::isDirectory($webRoutesPath)) {
            $files = $this->getFilesSortedByName($webRoutesPath);
            foreach ($files as $file) {
                $this->loadWebRoute($file);
            }
        }
    }

    private function loadWebRoute(SplFileInfo $file): void
    {
        Route::group([
            'middleware' => ['web'],
        ], static function () use ($file) {
            require $file->getPathname();
        });
    }
}
