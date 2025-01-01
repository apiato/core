<?php

namespace Apiato\Foundation;

use Apiato\Foundation\Configuration\ApplicationBuilder;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Middlewares\ProcessETag;
use Apiato\Foundation\Middlewares\Profiler;
use Apiato\Foundation\Middlewares\ValidateJsonContent;
use Apiato\Foundation\Support\PathHelper;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\SplFileInfo;

final class Apiato
{
    private static self $instance;
    private array $providerPaths = [];
    private array $configPaths = [];
    private array $listenerPaths = [];
    private array $commandPaths = [];
    private array $helperPaths = [];
    private Localization $localization;

    private function __construct(
        private string $basePath,
    ) {
    }

    public static function configure(string|null $basePath = null): ApplicationBuilder
    {
        if (isset(self::$instance)) {
            return new ApplicationBuilder(self::$instance);
        }

        $basePath = match (true) {
            is_string($basePath) => $basePath,
            default => self::inferBasePath(),
        };

        self::$instance = new self($basePath);

        return (new ApplicationBuilder(self::$instance))
            ->withProviders(
                $basePath . '/app/Ship/Providers',
                ...glob($basePath . '/app/Containers/*/*/Providers', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withConfigs(
                $basePath . '/app/Ship/Configs',
                ...glob($basePath . '/app/Containers/*/*/Configs', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withEvents(
                $basePath . '/app/Ship/Listeners',
                ...glob($basePath . '/app/Containers/*/*/Listeners', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withCommands(
                $basePath . '/app/Ship/Commands',
                ...glob($basePath . '/app/Containers/*/*/UI/Console', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withHelpers(
                $basePath . '/app/Ship/Helpers',
            )->withTranslations();
    }

    /**
     * Infer the application's base directory from the environment.
     */
    public static function inferBasePath(): string
    {
        return match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],
            default => dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]),
        };
    }

    public static function instance(): self
    {
        return self::$instance;
    }

    public function withProviders(string ...$path): self
    {
        $this->providerPaths = $path;

        return $this;
    }

    public function withTranslations(callable|null $callback = null): self
    {
        $this->localization = (new Localization())
            ->loadTranslationsFrom(
            $this->basePath . '/app/Ship/Languages',
            ...glob($this->basePath . '/app/Containers/*/*/Languages', GLOB_ONLYDIR | GLOB_NOSORT),
        );

        if (!is_null($callback)) {
            $callback($this->localization);
        }

        return $this;
    }

    public function withConfigs(string ...$path): void
    {
        $this->configPaths = $path;
    }

    public function withEvents(string ...$path): void
    {
        $this->listenerPaths = $path;
    }

    public function withCommands(string ...$path): void
    {
        $this->commandPaths = $path;
    }

    public function withHelpers(string ...$path): void
    {
        $this->helperPaths = $path;
    }

    public function providerPaths(): array
    {
        return $this->providerPaths;
    }

    public function configPaths(): array
    {
        return $this->configPaths;
    }

    public function helperPaths(): array
    {
        return $this->helperPaths;
    }

    public function localization(): Localization
    {
        return $this->localization;
    }

    public function events(): array
    {
        return $this->listenerPaths;
    }

    public function apiMiddlewares(): array
    {
        return [
            ValidateJsonContent::class,
            ProcessETag::class,
            Profiler::class,
        ];
    }

    public function commands(): array
    {
        return $this->commandPaths;
    }

    // TODO: separate Api and Web route registration
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
