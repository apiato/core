<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Foundation\Apiato;

final readonly class ApplicationBuilder
{
    public function __construct(
        private Apiato $apiato,
    ) {
        $this->withDefaults($this->apiato->basePath());
    }

    private function withDefaults(string $basePath): void
    {
        $this->withProviders(
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
            ...glob($basePath . '/app/Containers/*/*/Helpers', GLOB_ONLYDIR | GLOB_NOSORT),
        )->withMigrations(
            $basePath . '/app/Ship/Data/Migrations',
            ...glob($basePath . '/app/Containers/*/*/Data/Migrations', GLOB_ONLYDIR | GLOB_NOSORT),
        )->withSeeders(static function (Seeding $seeding) use ($basePath) {
            $seeding->loadFrom(
                ...glob($basePath . '/app/Containers/*/*/Data/Seeders', GLOB_ONLYDIR | GLOB_NOSORT),
            );
        })->withTranslations(static function (Localization $localization) use ($basePath) {
            $localization->loadFrom(
                $basePath . '/app/Ship/Languages',
                ...glob($basePath . '/app/Containers/*/*/Languages', GLOB_ONLYDIR | GLOB_NOSORT),
            );
        })->withViews(static function (View $view) use ($basePath) {
            $view->loadFrom(
                $basePath . '/app/Ship/Views',
                $basePath . '/app/Ship/Mails',
                ...glob($basePath . '/app/Containers/*/*/Views', GLOB_ONLYDIR | GLOB_NOSORT),
                ...glob($basePath . '/app/Containers/*/*/Mails', GLOB_ONLYDIR | GLOB_NOSORT),
            );
        })->withRouting(static function (Routing $routing) use ($basePath) {
            $routing->loadApiRoutesFrom(
                ...glob($basePath . '/app/Containers/*/*/UI/API/Routes', GLOB_ONLYDIR | GLOB_NOSORT),
            )->loadWebRoutesFrom(
                ...glob($basePath . '/app/Containers/*/*/UI/WEB/Routes', GLOB_ONLYDIR | GLOB_NOSORT),
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

    public function create(): Apiato
    {
        return $this->apiato;
    }
}
