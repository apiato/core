<?php

namespace Apiato\Core\Providers;

use Apiato\Core\Abstracts\Providers\AggregateServiceProvider;
use Apiato\Core\Generator\GeneratorsServiceProvider;
use Apiato\Core\Loaders\Apiato;
use Apiato\Core\Loaders\CommandLoaderTrait;
use Apiato\Core\Loaders\HelperLoaderTrait;
use Apiato\Core\Loaders\LanguageLoaderTrait;
use Apiato\Core\Loaders\MigrationLoaderTrait;
use Apiato\Core\Loaders\ViewLoaderTrait;
use Apiato\Core\Providers\MacroProviders\CollectionMacroServiceProvider;
use Apiato\Core\Providers\MacroProviders\ConfigMacroServiceProvider;
use Apiato\Core\Utilities\PathHelper;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Infrastructure\Fakes\Providers\FirstServiceProvider;
use Tests\Infrastructure\Fakes\Providers\SecondServiceProvider;

class ApiatoServiceProvider extends AggregateServiceProvider
{
    use CommandLoaderTrait;
    use HelperLoaderTrait;
    use LanguageLoaderTrait;
    use MigrationLoaderTrait;
    use ViewLoaderTrait;

    protected $providers = [
        GeneratorsServiceProvider::class,
        CollectionMacroServiceProvider::class,
        ConfigMacroServiceProvider::class,
    ];

    public function register(): void
    {
        $this->providers = $this->mergeProviders($this->providers, $this->serviceProviders());

        $this->runRegister();

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
        foreach (Apiato::providerPaths() as $directory) {
            foreach (File::files($directory) as $file) {
                $fqcn = PathHelper::getFQCNFromFile($file);
                $providers[] = $fqcn;
            }
        }

        return $providers;
    }

    private function mergeConfigs(): void
    {
        // The order of these statements matter! DO NOT CHANGE!
        foreach (Apiato::configPaths() as $dir) {
            foreach (File::files($dir) as $file) {
                $this->mergeConfigFrom($file->getPathname(), $file->getFilenameWithoutExtension());
            }
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/apiato.php',
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
        $this->runLoadersBoot();

        $this->publishes([
            __DIR__ . '/../../config/apiato.php' => app_path('Ship/Configs/apiato.php'),
        ], 'apiato-config');

        $this->configureRateLimiting(); // TODO: move to route service provider

        AboutCommand::add('Apiato', static fn () => ['Version' => '13.0.0']);
    }

    public function runLoadersBoot(): void
    {
        $this->loadShipMigrations();
        $this->loadShipLanguages();
        $this->loadShipViews();
        $this->loadShipHelpers();
        $this->loadCoreCommands();

        foreach (PathHelper::getContainerPaths() as $containerPath) {
            $this->loadContainerMigrations($containerPath);
            $this->loadContainerLanguages($containerPath);
            $this->loadContainerViews($containerPath);
            $this->loadContainerHelpers($containerPath);
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
