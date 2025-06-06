<?php

namespace Apiato\Foundation;

use Apiato\Foundation\Configuration\ApplicationBuilder;
use Apiato\Foundation\Configuration\Factory;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Configuration\Provider;
use Apiato\Foundation\Configuration\Repository;
use Apiato\Foundation\Configuration\Routing;
use Apiato\Foundation\Configuration\Seeding;
use Apiato\Foundation\Configuration\View;
use Composer\Autoload\ClassLoader;

use function Illuminate\Filesystem\join_paths;

final class Apiato
{
    private static self $instance;
    private string $sharedPath;
    /** @var string[] */
    private array $configPaths = [];
    /** @var string[] */
    private array $eventDiscoveryPaths = [];
    /** @var string[] */
    private array $commandPaths = [];
    /** @var string[] */
    private array $migrationPaths = [];
    /** @var string[] */
    private array $helperPaths = [];
    private Routing $routing;
    private Localization $localization;
    private View $view;
    private Seeding $seeding;
    private Factory $factory;
    private Repository $repository;
    private Provider $provider;

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

        return new ApplicationBuilder(self::$instance);
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

    /**
     * Get the singleton instance of the class.
     */
    public static function instance(): self
    {
        return self::$instance;
    }

    /**
     * Reset the configured instance to its default state.
     */
    public static function reset(): self
    {
        self::$instance = new self(self::$instance->basePath);

        return (new ApplicationBuilder(self::$instance))->create();
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get the path to the application's shared directory.
     */
    public function sharedPath(string $path = ''): string
    {
        return join_paths($this->sharedPath ?: app_path('Ship'), $path);
    }

    /**
     * Set the shared directory path.
     */
    public function useSharedPath(string $path): self
    {
        $this->sharedPath = $path;

        return $this;
    }

    public function withRouting(callable|null $callback = null): self
    {
        $this->routing ??= new Routing();

        if (!is_null($callback)) {
            $callback($this->routing);
        }

        return $this;
    }

    public function withFactories(callable|null $callback = null): self
    {
        $this->factory ??= new Factory();

        if (!is_null($callback)) {
            $callback($this->factory);
        }

        return $this;
    }

    public function withRepositories(callable|null $callback = null): self
    {
        $this->repository ??= new Repository();

        if (!is_null($callback)) {
            $callback($this->repository);
        }

        return $this;
    }

    public function withViews(callable|null $callback = null): self
    {
        $this->view ??= new View();

        if (!is_null($callback)) {
            $callback($this->view);
        }

        return $this;
    }

    public function withTranslations(callable|null $callback = null): self
    {
        $this->localization ??= new Localization();

        if (!is_null($callback)) {
            $callback($this->localization);
        }

        return $this;
    }

    public function withSeeders(callable|null $callback = null): self
    {
        $this->seeding ??= new Seeding();

        if (!is_null($callback)) {
            $callback($this->seeding);
        }

        return $this;
    }

    public function withProviders(callable|null $callback = null): self
    {
        $this->provider ??= new Provider();

        if (!is_null($callback)) {
            $callback($this->provider);
        }

        return $this;
    }

    public function withMigrations(string ...$path): self
    {
        $this->migrationPaths = $path;

        return $this;
    }

    public function withHelpers(string ...$path): void
    {
        $this->helperPaths = $path;
    }

    public function withCommands(string ...$path): void
    {
        $this->commandPaths = $path;
    }

    public function withEvents(string ...$path): void
    {
        $this->eventDiscoveryPaths = $path;
    }

    public function withConfigs(string ...$path): void
    {
        $this->configPaths = $path;
    }

    /*
     * Get the config files.
     *
     * @return string[]
     */
    public function configs(): array
    {
        return collect($this->configPaths)->flatMap(
            static fn (string $path): array => \Safe\glob($path . '/*.php'),
        )->toArray();
    }

    /*
     * Get the helper files.
     *
     * @return string[]
     */
    public function helpers(): array
    {
        return collect($this->helperPaths)->flatMap(
            static fn (string $path): array => \Safe\glob($path . '/*.php'),
        )->toArray();
    }

    /**
     * Get the migration paths.
     */
    public function migrations(): array
    {
        return $this->migrationPaths;
    }

    /**
     * Get the event paths.
     */
    public function events(): array
    {
        return $this->eventDiscoveryPaths;
    }

    /**
     * Get the command paths.
     */
    public function commands(): array
    {
        return $this->commandPaths;
    }

    /**
     * Register the API routes.
     */
    public function registerApiRoutes(): void
    {
        $this->routing->registerApiRoutes();
    }

    /**
     * Get Web routes.
     */
    public function webRoutes(): array
    {
        return $this->routing->webRoutes();
    }

    public function providers(): array
    {
        return $this->provider->toArray();
    }

    /**
     * Get the seeding configuration.
     */
    public function seeding(): Seeding
    {
        return $this->seeding;
    }

    /**
     * Get the routing configuration.
     */
    public function routing(): Routing
    {
        return $this->routing;
    }

    /**
     * Get the factory configuration.
     */
    public function factory(): Factory
    {
        return $this->factory;
    }

    /**
     * Get the repository configuration.
     */
    public function repository(): Repository
    {
        return $this->repository;
    }

    /**
     * Get the provider configuration.
     */
    public function provider(): Provider
    {
        return $this->provider;
    }

    /**
     * Get the localization configuration.
     */
    public function localization(): Localization
    {
        return $this->localization;
    }

    /**
     * Get the view configuration.
     */
    public function view(): View
    {
        return $this->view;
    }
}
