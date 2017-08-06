<?php

namespace Apiato\Core\Providers;

use Apiato\Core\Loaders\AutoLoaderTrait;
use Apiato\Core\Loaders\FactoriesLoaderTrait;
use Apiato\Core\Generator\GeneratorsServiceProvider;
use Apiato\Core\Traits\ValidationTrait;
use Apiato\Core\Butlers\ContainersButler;
use Apiato\Core\Butlers\ShipButler;
use App\Ship\Parents\Providers\MainProvider;
use App\Ship\Parents\Providers\RoutesProvider;
use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;
use Illuminate\Support\Facades\Schema;
use Prettus\Repository\Providers\RepositoryServiceProvider;
use Vinkla\Hashids\Facades\Hashids;
use Vinkla\Hashids\HashidsServiceProvider;
use Spatie\Fractal\FractalServiceProvider;
use Spatie\Fractal\FractalFacade;
use App\Ship\Providers\ServiceProvider;

/**
 * The App Service Provider where all Service Providers gets registered
 * this is the only Service Provider that gets injected in the Config/app.php.
 *
 * A.K.A app/Providers/AppServiceProvider.php
 *
 * Class MainServiceProvider
 *
 * @author  Mahmoud Zalt <mahmoud@zalt.me>
 */
class PortoServiceProvider extends MainProvider
{
    use FactoriesLoaderTrait;
    use AutoLoaderTrait;
    use ValidationTrait;

    /**
     * Register any Service Providers on the Ship layer (including third party packages).
     *
     * @var array
     */
    public $serviceProviders = [
        CorsServiceProvider::class,
        HashidsServiceProvider::class,
        RoutesProvider::class,
        RepositoryServiceProvider::class,
        GeneratorsServiceProvider::class,
        FractalServiceProvider::class,
        ServiceProvider::class,
    ];

    /**
     * Register any Alias on the Ship layer (including third party packages).
     *
     * @var  array
     */
    protected $aliases = [
        'Hashids' => Hashids::class,
        'Fractal' => FractalFacade::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Autoload most of the Containers and Ship Components
        $this->runLoadersBoot();

        // load all service providers defined in this class
        parent::boot();

        // Solves the "specified key was too long" error, introduced in L5.4
        Schema::defaultStringLength(191);

        // Registering custom validation rules
        $this->extendValidationRules();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        // Register Engine Facade Classes
        $this->app->alias(ShipButler::class, 'ShipButler');
        $this->app->alias(ContainersButler::class, 'ContainersButler');
    }

}
