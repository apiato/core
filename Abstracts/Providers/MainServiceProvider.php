<?php

namespace Apiato\Core\Abstracts\Providers;

use Apiato\Core\Loaders\AliasesLoaderTrait;
use Apiato\Core\Loaders\ProvidersLoaderTrait;
use Illuminate\Support\ServiceProvider as LaravelAppServiceProvider;

abstract class MainServiceProvider extends LaravelAppServiceProvider
{
    use ProvidersLoaderTrait;
    use AliasesLoaderTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->loadServiceProviders();
        $this->loadAliases();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {

    }
}
