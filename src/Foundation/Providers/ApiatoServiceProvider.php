<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Database\DatabaseSeeder;
use Apiato\Foundation\Support\Providers\ConfigServiceProvider;
use Apiato\Foundation\Support\Providers\HelperServiceProvider;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;
use Apiato\Foundation\Support\Providers\MigrationServiceProvider;
use Apiato\Foundation\Support\Providers\RateLimitingServiceProvider;
use Apiato\Foundation\Support\Providers\ViewServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Console\AboutCommand;

final class ApiatoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(GeneratorsServiceProvider::class);
        $this->app->register(MacroServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
        $this->app->register(ConfigServiceProvider::class);
        $this->app->register(HelperServiceProvider::class);
        $this->app->register(LocalizationServiceProvider::class);
        $this->app->register(MigrationServiceProvider::class);
        $this->app->register(RateLimitingServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);

        AliasLoader::getInstance()->alias('DatabaseSeeder', DatabaseSeeder::class);

        $this->app->singletonIf(Apiato::class, static fn () => Apiato::instance());
    }

    public function boot(): void
    {
        AboutCommand::add('Apiato', static fn () => ['Version' => '13.0.0']);
    }
}
