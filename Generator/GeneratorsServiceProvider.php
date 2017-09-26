<?php

namespace Apiato\Core\Generator;

use Apiato\Core\Generator\Commands\ActionGenerator;
use Apiato\Core\Generator\Commands\ControllerGenerator;
use Apiato\Core\Generator\Commands\ExceptionGenerator;
use Apiato\Core\Generator\Commands\ModelGenerator;
use Apiato\Core\Generator\Commands\NotificationGenerator;
use Apiato\Core\Generator\Commands\RepositoryGenerator;
use Apiato\Core\Generator\Commands\RequestGenerator;
use Apiato\Core\Generator\Commands\RouteGenerator;
use Apiato\Core\Generator\Commands\SubActionGenerator;
use Apiato\Core\Generator\Commands\TaskGenerator;
use Apiato\Core\Generator\Commands\TransformerGenerator;
use Illuminate\Support\ServiceProvider;

/**
 * Class GeneratorsServiceProvider
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class GeneratorsServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGenerators([
            ActionGenerator::class,
            ControllerGenerator::class,
            ExceptionGenerator::class,
            ModelGenerator::class,
            NotificationGenerator::class,
            RepositoryGenerator::class,
            RequestGenerator::class,
            RouteGenerator::class,
            SubActionGenerator::class,
            TaskGenerator::class,
            TransformerGenerator::class,
        ]);
    }

    /**
     * Register the generators.
     * @param array $classes
     */
    private function registerGenerators(array $classes)
    {
        foreach ($classes as $class) {
            $lowerClass = strtolower($class);

            $this->app->singleton("command.porto.$lowerClass", function ($app) use ($class) {
                return $app[$class];
            });

            $this->commands("command.porto.$lowerClass");
        }
    }
}
