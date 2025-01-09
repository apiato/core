<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider;
use Apiato\Commands\ListActions;
use Apiato\Commands\ListTasks;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Database\DatabaseSeeder;
use Apiato\Foundation\Support\PathHelper;
use Apiato\Foundation\Support\Providers\HelperServiceProvider;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;
use Apiato\Foundation\Support\Providers\MigrationServiceProvider;
use Apiato\Foundation\Support\Providers\RateLimitingServiceProvider;
use Apiato\Foundation\Support\Providers\ViewServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\File;

class ApiatoServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        GeneratorsServiceProvider::class,
        MacroServiceProvider::class,
        HelperServiceProvider::class,
        LocalizationServiceProvider::class,
        MigrationServiceProvider::class,
        RateLimitingServiceProvider::class,
        ViewServiceProvider::class,
    ];

    protected array $aliases = [
        'DatabaseSeeder' => DatabaseSeeder::class,
    ];

    public function register(): void
    {
        $this->providers = $this->mergeProviders(
            $this->providers,
            $this->serviceProviders(),
        );

        $this->registerRecursive();

        $this->registerCoreCommands();

        $this->app->singletonIf(Apiato::class, static fn () => Apiato::instance());

        $this->mergeConfigs();
    }

    private function mergeProviders(array ...$providers): array
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

    public function boot(): void
    {
        AboutCommand::add('Apiato', static fn () => ['Version' => '13.0.0']);

        $this->publishes([
            __DIR__ . '/../../../config/apiato.php' => app_path('Ship/Configs/apiato.php'),
        ], 'apiato-config');
    }
}
