<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider;
use Apiato\Commands\ListActions;
use Apiato\Commands\ListTasks;
use Apiato\Commands\SeedDeploymentData;
use Apiato\Commands\SeedTestingData;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Loaders\HelperLoader;
use Apiato\Foundation\Loaders\Loader;
use Apiato\Foundation\Loaders\MigrationLoaderTrait;
use Apiato\Foundation\Support\PathHelper;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;
use Apiato\Foundation\Support\Providers\ViewServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\Doubles\Fakes\Providers\FirstServiceProvider;
use Tests\Support\Doubles\Fakes\Providers\SecondServiceProvider;

class ApiatoServiceProvider extends AggregateServiceProvider
{
    use MigrationLoaderTrait;

    protected $providers = [
        GeneratorsServiceProvider::class,
        MacroServiceProvider::class,
        LocalizationServiceProvider::class,
        ViewServiceProvider::class,
    ];

    public function register(): void
    {
        $this->providers = $this->mergeProviders($this->providers, $this->serviceProviders());

        $this->runRegister();
        $this->registerCoreCommands();

        $this->mergeConfigs();

        $this->booting(function (Application $app) {
            $this->setUpTestProviders($app);
        });
    }

    public function mergeProviders(array ...$providers): array
    {
        return collect($providers)
            ->flatten()
            ->unique()
            ->toArray();
    }

    private function serviceProviders(): array
    {
        $providers = [];
        foreach (Apiato::instance()->providerPaths() as $directory) {
            foreach (File::files($directory) as $file) {
                $fqcn = PathHelper::getFQCNFromFile($file);
                $providers[] = $fqcn;
            }
        }

        return $providers;
    }

    private function registerCoreCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ListActions::class,
                ListTasks::class,
                SeedDeploymentData::class,
                SeedTestingData::class,
            ]);
        }
    }

    private function mergeConfigs(): void
    {
        // The order of these statements matter! DO NOT CHANGE!
        foreach (Apiato::instance()->configPaths() as $dir) {
            foreach (File::files($dir) as $file) {
                $this->mergeConfigFrom($file->getPathname(), $file->getFilenameWithoutExtension());
            }
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/apiato.php',
            'apiato',
        );
    }

    // TODO: can we NOT do this and move the providers in the fake Laravel app?
    private function setUpTestProviders(Application $app): void
    {
        $currentProviders = $this->providers;
        if ($app['config']->get('core.tests.running')) {
            $this->providers = [
                FirstServiceProvider::class,
                SecondServiceProvider::class,
            ];
            $this->runRegister();
            $this->providers = $currentProviders;
        }
    }

    public function boot(): void
    {
        $this->runBoot();

        $this->runLoaders();

        $this->publishes([
            __DIR__ . '/../../../config/apiato.php' => app_path('Ship/Configs/apiato.php'),
        ], 'apiato-config');

        $this->configureRateLimiting(); // TODO: move to route service provider

        AboutCommand::add('Apiato', static fn () => ['Version' => '13.0.0']);
    }

    public function runLoaders(): void
    {
        $this->load(
            HelperLoader::create(),
        );

        //        $this->loadShipMigrations();

        foreach (PathHelper::getContainerPaths() as $containerPath) {
            //            $this->loadContainerMigrations($containerPath);
        }
    }

    private function load(Loader ...$loader): void
    {
        foreach ($loader as $load) {
            $load->load();
        }
    }

    protected function configureRateLimiting(): void
    {
        if (config('apiato.api.rate-limiter.enabled')) {
            RateLimiter::for(
                config('apiato.api.rate-limiter.name'),
                static function (Request $request) {
                    return Limit::perMinutes(
                        config('apiato.api.rate-limiter.expires'),
                        config('apiato.api.rate-limiter.attempts'),
                    )->by($request->user()?->id ?: $request->ip());
                },
            );
        }
    }
}
