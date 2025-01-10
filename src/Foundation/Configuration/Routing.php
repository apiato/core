<?php

namespace Apiato\Foundation\Configuration;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as LaravelRoute;
use Symfony\Component\Finder\SplFileInfo;

final class Routing
{
    private array $apiRouteDirs = [];
    private array $webRouteDirs = [];

    public function loadApiRoutesFrom(string ...$path): self
    {
        $this->apiRouteDirs = $path;

        return $this;
    }

    public function loadWebRoutesFrom(string ...$path): self
    {
        $this->webRouteDirs = $path;

        return $this;
    }

    public function registerApiRoutes(): void
    {
        collect($this->apiRouteDirs)
            ->map(fn ($path) => $this->getFilesSortedByName($path))
            ->flatten()
            ->each(fn (SplFileInfo $file) => $this->loadApiRoute($file));
    }

    /**
     * @return SplFileInfo[]
     */
    private function getFilesSortedByName(string $apiRoutesPath): array
    {
        $files = File::allFiles($apiRoutesPath);

        return Arr::sort($files, static fn ($file) => $file->getFilename());
    }

    private function loadApiRoute(SplFileInfo $file): void
    {
        $routeGroupArray = $this->getApiRouteGroup($file);

        LaravelRoute::group($routeGroupArray, static function () use ($file): void {
            require $file->getPathname();
        });
    }

    private function getApiRouteGroup(SplFileInfo|string $endpointFileOrPrefixString): array
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

    public function webRoutes(): array
    {
        $files = [];
        foreach ($this->webRouteDirs as $path) {
            foreach (glob($path . '/*.php') as $file) {
                $files[] = $file;
            }
        }
        usort($files, 'strcmp');

        return $files;
    }
}
