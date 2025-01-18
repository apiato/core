<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Foundation\Apiato;
use Safe\Exceptions\FilesystemException;

use function Illuminate\Filesystem\join_paths;

final readonly class ApplicationBuilder
{
    public function __construct(
        private Apiato $apiato,
    ) {
        $this->withDefaults($this->apiato->basePath());
    }

    private function withDefaults(string $basePath): void
    {
        $this->useSharedPath(
            $this->joinPaths($basePath, 'app/Ship'),
        )->withProviders(
            shared_path('Providers'),
            ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Providers')),
        )->withConfigs(
            shared_path('Configs'),
            ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Configs')),
        )->withEvents(
            shared_path('Listeners'),
            ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Listeners')),
        )->withCommands(
            shared_path('Commands'),
            ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/UI/Console')),
        )->withHelpers(
            shared_path('Helpers'),
            ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Helpers')),
        )->withMigrations(
            shared_path('Data/Migrations'),
            ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Data/Migrations')),
        )->withSeeders(function (Seeding $seeding) use ($basePath): void {
            $seeding->loadFrom(
                ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Data/Seeders')),
            );
        })->withTranslations(function (Localization $localization) use ($basePath): void {
            $localization->loadFrom(
                shared_path('Languages'),
                ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Languages')),
            );
        })->withViews(function (View $view) use ($basePath): void {
            $view->loadFrom(
                shared_path('Views'),
                shared_path('Mails'),
                ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Views')),
                ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/Mails')),
            );
        })->withRouting(function (Routing $routing) use ($basePath): void {
            $routing->loadApiRoutesFrom(
                ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/UI/API/Routes')),
            )->loadWebRoutesFrom(
                ...$this->getDirs($this->joinPaths($basePath, 'app/Containers/*/*/UI/WEB/Routes')),
            );
        })->withFactories();
    }

    public function withFactories(callable|null $callback = null): self
    {
        $this->apiato->withFactories($callback);

        return $this;
    }

    public function withRouting(callable|null $callback = null): self
    {
        $this->apiato->withRouting($callback);

        return $this;
    }

    public function withViews(callable|null $callback = null): self
    {
        $this->apiato->withViews($callback);

        return $this;
    }

    public function withTranslations(callable|null $callback = null): self
    {
        $this->apiato->withTranslations($callback);

        return $this;
    }

    public function withSeeders(callable|null $callback = null): self
    {
        $this->apiato->withSeeders($callback);

        return $this;
    }

    public function withMigrations(string ...$path): self
    {
        $this->apiato->withMigrations(...$path);

        return $this;
    }

    public function withHelpers(string ...$path): self
    {
        $this->apiato->withHelpers(...$path);

        return $this;
    }

    public function withCommands(string ...$path): self
    {
        $this->apiato->withCommands(...$path);

        return $this;
    }

    public function withEvents(string ...$path): self
    {
        $this->apiato->withEvents(...$path);

        return $this;
    }

    public function withConfigs(string ...$path): self
    {
        $this->apiato->withConfigs(...$path);

        return $this;
    }

    public function withProviders(string ...$path): self
    {
        $this->apiato->withProviders(...$path);

        return $this;
    }

    /**
     * Set the shared directory path.
     */
    public function useSharedPath(string $path): self
    {
        $this->apiato->useSharedPath($path);

        return $this;
    }

    private function joinPaths(string $basePath, string $path = ''): string
    {
        return join_paths($basePath, $path);
    }

    /**
     * @return string[]
     *
     * @throws FilesystemException
     */
    private function getDirs(string $pattern): array
    {
        /** @var string[] $dirs */
        $dirs = \Safe\glob($pattern, GLOB_ONLYDIR | GLOB_NOSORT);

        return $dirs;
    }

    public function create(): Apiato
    {
        return $this->apiato;
    }
}
