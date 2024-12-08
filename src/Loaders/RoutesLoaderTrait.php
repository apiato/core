<?php

namespace Apiato\Core\Loaders;

use Apiato\Core\Foundation\Facades\Apiato;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\SplFileInfo;

trait RoutesLoaderTrait
{
    /**
     * Register all the containers routes files in the framework.
     */
    public function runRoutesAutoLoader(): void
    {
        if (!$this->shouldRegisterRoutes()) {
            return;
        }

        $allContainerPaths = Apiato::getAllContainerPaths();

        foreach ($allContainerPaths as $containerPath) {
            $this->loadApiContainerRoutes($containerPath);
            $this->loadWebContainerRoutes($containerPath);
        }
    }

    private function shouldRegisterRoutes(): bool
    {
        if ($this->app->routesAreCached()) {
            return false;
        }

        return true;
    }

    /**
     * Register the Containers API routes files.
     */
    private function loadApiContainerRoutes(string $containerPath): void
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
        return $this->getUIPathForContainer($containerPath, $ui) . DIRECTORY_SEPARATOR . 'Routes';
    }

    private function getUIPathForContainer(string $containerPath, string $ui): string
    {
        return $containerPath . DIRECTORY_SEPARATOR . 'UI' . DIRECTORY_SEPARATOR . $ui;
    }

    /**
     * @return array|SplFileInfo[]
     */
    private function getFilesSortedByName(string $apiRoutesPath): array
    {
        $files = File::allFiles($apiRoutesPath);
        $files = Arr::sort($files, function ($file) {
            return $file->getFilename();
        });

        return $files;
    }

    private function loadApiRoute(SplFileInfo $file): void
    {
        $routeGroupArray = $this->getApiRouteGroup($file);

        Route::group($routeGroupArray, function ($router) use ($file) {
            require $file->getPathname();
        });
    }

    public function getApiRouteGroup(SplFileInfo|string $endpointFileOrPrefixString): array
    {
        return [
            'middleware' => $this->getMiddlewares(),
            'domain' => $this->getApiUrl(),
            // If $endpointFileOrPrefixString is a string, use that string as prefix
            // else, if it is a file then get the version name from the file name, and use it as prefix
            'prefix' => is_string($endpointFileOrPrefixString) ? $endpointFileOrPrefixString : $this->getApiVersionPrefix($endpointFileOrPrefixString),
        ];
    }

    private function getMiddlewares(): array
    {
        return array_filter([
            'api',
            $this->getRateLimitMiddleware(), // Returns NULL if feature disabled. Null will be removed form the array.
        ]);
    }

    private function getRateLimitMiddleware(): string|null
    {
        $rateLimitMiddleware = null;

        if (config('apiato.api.throttle.enabled')) {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinutes(config('apiato.api.throttle.expires'), config('apiato.api.throttle.attempts'))->by($request->user()?->id ?: $request->ip());
            });

            $rateLimitMiddleware = 'throttle:api';
        }

        return $rateLimitMiddleware;
    }

    private function getApiUrl(): string
    {
        return config('apiato.api.url');
    }

    private function getApiVersionPrefix(SplFileInfo $file): string
    {
        return config('apiato.api.prefix') . (config('apiato.api.enable_version_prefix') ? $this->getRouteFileVersionFromFileName($file) : '');
    }

    // TODO: Refactor. Ask Isiah about his thoughts on this.
    //  I think we should abstract this.
    //  and the user should be able to provide his logic for parsing the version from the file name.
    //  Maybe we can extract it into a class that implements an interface that the user can implement?
    //   - This way we can provide a default implementation that the user can override.
    //  This implementation is also very limiting as it only allows the versioning to be disabled as a whole or enabled.
    //   - What if the user whats to use another versioning strategy?
    //   - What if the user wants to use a different versioning just for some routes?
    //  The current implementation is also very coupled with the file name.
    //   If versioning is enabled:
    //   - It expects version to be the one element before the last one (split on ".").
    //   - If user forgets to add the version to the file name, it will not work.
    //      And we will have a problem!
    private function getRouteFileVersionFromFileName(SplFileInfo $file): string|bool
    {
        $fileNameWithoutExtension = $this->getRouteFileNameWithoutExtension($file);

        $fileNameWithoutExtensionExploded = explode('.', $fileNameWithoutExtension);

        end($fileNameWithoutExtensionExploded);

        // get the array before the last one
        return prev($fileNameWithoutExtensionExploded);
    }

    private function getRouteFileNameWithoutExtension(SplFileInfo $file): string
    {
        return pathinfo($file->getFilename(), PATHINFO_FILENAME);
    }

    /**
     * Register the Containers WEB routes files.
     */
    private function loadWebContainerRoutes($containerPath): void
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
        ], function ($router) use ($file) {
            require $file->getPathname();
        });
    }
}
