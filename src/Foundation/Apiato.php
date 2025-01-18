<?php

namespace Apiato\Foundation;

use Apiato\Foundation\Configuration\ApplicationBuilder;
use Apiato\Foundation\Configuration\FactoryDiscovery;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Configuration\Routing;
use Apiato\Foundation\Configuration\Seeding;
use Apiato\Foundation\Configuration\View;
use Apiato\Foundation\Middleware\ProcessETag;
use Apiato\Foundation\Middleware\Profiler;
use Apiato\Foundation\Middleware\ValidateJsonContent;
use Composer\Autoload\ClassLoader;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Safe\Exceptions\FilesystemException;

use function Illuminate\Filesystem\join_paths;

class Apiato
{
    private static self $instance;
    private string $sharedPath;
    private array $providerPaths = [];
    private array $configPaths = [];
    private array $eventDiscoveryPaths = [];
    private array $commandPaths = [];
    private array $migrationPaths = [];
    private array $helperPaths = [];
    private Routing $routing;
    private Localization $localization;
    private View $view;
    private Seeding $seeding;
    private FactoryDiscovery $factoryDiscovery;

    private function __construct(
        private readonly string $basePath,
    ) {
    }

    /**
     * @throws FilesystemException
     */
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
     * TODO: add arch tests to make sure this method is only used in ApiatoServiceProvider.
     */
    public static function instance(): self
    {
        return self::$instance;
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
        $this->factoryDiscovery ??= new FactoryDiscovery();

        if (!is_null($callback)) {
            $callback($this->factoryDiscovery);
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

    public function withProviders(string ...$path): self
    {
        $this->providerPaths = $path;

        return $this;
    }

    /*
     * Get the service providers to be loaded.
     *
     * @return string[]
     */
    public function providers(): array
    {
        $classMapper = new ClassMapGenerator();
        foreach ($this->providerPaths as $path) {
            $classMapper->scanPaths($path);
        }

        return array_keys($classMapper->getClassMap()->getMap());
    }

    /*
     * Get the configuration files to be loaded.
     *
     * @return string[]
     */
    public function configs(): array
    {
        return collect($this->configPaths)->flatMap(
            static fn (string $path) => \Safe\glob($path . '/*.php'),
        )->toArray();
    }

    /*
     * Get the helper files to be loaded.
     *
     * @return string[]
     */
    public function helpers(): array
    {
        return collect($this->helperPaths)->flatMap(
            static fn (string $path) => \Safe\glob($path . '/*.php'),
        )->toArray();
    }

    public function migrationPaths(): array
    {
        return $this->migrationPaths;
    }

    public function events(): array
    {
        return $this->eventDiscoveryPaths;
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

    public function registerApiRoutes(): void
    {
        $this->routing->registerApiRoutes();
    }

    public function webRoutes(): array
    {
        return $this->routing->webRoutes();
    }

    public function seeding(): Seeding
    {
        return $this->seeding;
    }

    public function routing(): Routing
    {
        return $this->routing;
    }

    public function factoryDiscovery(): FactoryDiscovery
    {
        return $this->factoryDiscovery;
    }

    public function localization(): Localization
    {
        return $this->localization;
    }

    public function view(): View
    {
        return $this->view;
    }
}
