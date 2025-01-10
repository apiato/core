<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Database\DatabaseSeeder;
use Apiato\Foundation\Support\PathHelper;
use Apiato\Foundation\Support\Providers\CommandServiceProvider;
use Apiato\Foundation\Support\Providers\ConfigServiceProvider;
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
    protected array $providers = [
        GeneratorsServiceProvider::class,
        MacroServiceProvider::class,
        CommandServiceProvider::class,
        ConfigServiceProvider::class,
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
            $this->appProviders(),
        );

        parent::register();

        $this->app->singletonIf(Apiato::class, static fn () => Apiato::instance());
    }

    private function mergeProviders(array ...$providers): array
    {
        return collect($providers)
            ->flatten()
            ->unique()
            ->toArray();
    }

    private function appProviders(): array
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

    public function boot(): void
    {
        AboutCommand::add('Apiato', static fn () => ['Version' => '13.0.0']);
    }
}
