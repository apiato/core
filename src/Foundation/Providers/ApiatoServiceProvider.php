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
use Apiato\Support\HashidsManagerDecorator;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Console\AboutCommand;
use Vinkla\Hashids\HashidsManager;

final class ApiatoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ConfigServiceProvider::class);
        $this->app->register(HelperServiceProvider::class);
        $this->app->register(LocalizationServiceProvider::class);
        $this->app->register(MigrationServiceProvider::class);
        $this->app->register(RateLimitingServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);

        AliasLoader::getInstance()->alias('Database\\Seeders\\DatabaseSeeder', DatabaseSeeder::class);
        AliasLoader::getInstance()->alias('DatabaseSeeder', DatabaseSeeder::class);

        $this->app->singletonIf(Apiato::class, static fn (): Apiato => Apiato::instance());

        $this->app->extend('hashids', static function (HashidsManager $manager) {
            return new HashidsManagerDecorator($manager);
        });
    }

    public function boot(): void
    {
        AboutCommand::add('Apiato', static fn (): array => ['Version' => '13.0.0']);
    }
}
