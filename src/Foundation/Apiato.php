<?php

namespace Apiato\Foundation;

use Apiato\Foundation\Configuration\ApplicationBuilder;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Configuration\Routing;
use Apiato\Foundation\Configuration\Seeding;
use Apiato\Foundation\Configuration\View;
use Apiato\Foundation\Middleware\ProcessETag;
use Apiato\Foundation\Middleware\Profiler;
use Apiato\Foundation\Middleware\ValidateJsonContent;
use Composer\Autoload\ClassLoader;

class Apiato
{
    private static self $instance;
    private array $providerPaths = [];
    private array $configPaths = [];
    private array $listenerPaths = [];
    private array $commandPaths = [];
    private array $migrationPaths = [];
    private array $helperPaths = [];
    private Routing $routing;
    private Localization $localization;
    private View $view;
    private Seeding $seeding;

    private function __construct(
        private readonly string $basePath,
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
            )->withMigrations(
                $basePath . '/app/Ship/Data/Migrations',
                ...glob($basePath . '/app/Containers/*/*/Data/Migrations', GLOB_ONLYDIR | GLOB_NOSORT),
            )->withSeeders()
            ->withTranslations()
            ->withViews()
            ->withRouting();
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

    public function withRouting(callable|null $callback = null): self
    {
        // TODO: maybe make the configuration parametrized like web: api:, like the way Laravel does it?
        $this->routing = (new Routing())
            ->loadApiRoutesFrom(
                ...glob($this->basePath . '/app/Containers/*/*/UI/API/Routes', GLOB_ONLYDIR | GLOB_NOSORT),
            )->loadWebRoutesFrom(
                ...glob($this->basePath . '/app/Containers/*/*/UI/WEB/Routes', GLOB_ONLYDIR | GLOB_NOSORT),
            );

        if (!is_null($callback)) {
            $callback($this->routing);
        }

        return $this;
    }

    public function withTranslations(callable|null $callback = null): self
    {
        $this->localization = (new Localization())
            ->loadFrom(
                $this->basePath . '/app/Ship/Languages',
                ...glob($this->basePath . '/app/Containers/*/*/Languages', GLOB_ONLYDIR | GLOB_NOSORT),
            );

        if (!is_null($callback)) {
            $callback($this->localization);
        }

        return $this;
    }

    public function withSeeders(callable|null $callback = null): self
    {
        $this->seeding = (new Seeding())
            ->loadFrom(
                ...glob($this->basePath . '/app/Containers/*/*/Data/Seeders', GLOB_ONLYDIR | GLOB_NOSORT),
            );

        if (!is_null($callback)) {
            $callback($this->seeding);
        }

        return $this;
    }

    public function withViews(callable|null $callback = null): self
    {
        $this->view = (new View())
            ->loadFrom(
                $this->basePath . '/app/Ship/Views',
                $this->basePath . '/app/Ship/Mails',
                ...glob($this->basePath . '/app/Containers/*/*/Views', GLOB_ONLYDIR | GLOB_NOSORT),
                ...glob($this->basePath . '/app/Containers/*/*/Mails', GLOB_ONLYDIR | GLOB_NOSORT),
            );

        if (!is_null($callback)) {
            $callback($this->view);
        }

        return $this;
    }

    public function withHelpers(string ...$path): void
    {
        $this->helperPaths = $path;
    }

    public function withProviders(string ...$path): self
    {
        $this->providerPaths = $path;

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

    public function withMigrations(string ...$path): self
    {
        $this->migrationPaths = $path;

        return $this;
    }

    // TODO: add arch tests to make sure this method is only used in ApiatoServiceProvider
    public static function instance(): self
    {
        return self::$instance;
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

    public function migrationPaths(): array
    {
        return $this->migrationPaths;
    }

    public function seeding(): Seeding
    {
        return $this->seeding;
    }

    public function localization(): Localization
    {
        return $this->localization;
    }

    public function view(): View
    {
        return $this->view;
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

    public function registerRoutes(): void
    {
        $this->routing->registerApiRoutes();
    }

    public function webRoutes(): array
    {
        return $this->routing->webRoutes();
    }
}
