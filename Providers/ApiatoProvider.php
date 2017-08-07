<?php

namespace Apiato\Core\Providers;

use Apiato\Core\Abstracts\Providers\MainProvider as AbstractMainProvider;
use Apiato\Core\Butlers\ContainersButler;
use Apiato\Core\Butlers\ShipButler;
use Apiato\Core\Loaders\AutoLoaderTrait;
use Apiato\Core\Loaders\FactoriesLoaderTrait;
use Apiato\Core\Traits\ValidationTrait;
use App\Ship\Providers\ShipProvider;
use Illuminate\Support\Facades\Schema;

use App\Ship\Parents\Providers\MainProvider;
use Apiato\Core\Generator\GeneratorsServiceProvider;
use App\Ship\Parents\Providers\RoutesProvider;

use Barryvdh\Cors\ServiceProvider as CorsServiceProvider;
use Prettus\Repository\Providers\RepositoryServiceProvider;
use Spatie\Fractal\FractalFacade;
use Spatie\Fractal\FractalServiceProvider;
use Vinkla\Hashids\Facades\Hashids;
use Vinkla\Hashids\HashidsServiceProvider;

/**
 * Class ApiatoProviders
 *
 * Does not have to extend from the Ship parent MainProvider since it's on the Core
 * it directly extends from the Abstract MainProvider.
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class ApiatoProvider extends AbstractMainProvider
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
        GeneratorsServiceProvider::class,
        RoutesProvider::class,
        HashidsServiceProvider::class,
        RepositoryServiceProvider::class,
        CorsServiceProvider::class,
        FractalServiceProvider::class,
        ShipProvider::class, // Registering the ShipProvider at the end.
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
