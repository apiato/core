<?php

namespace Apiato\Core\Generator;

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
            'Action',
            'Controller',
            'Exception',
            'Model',
            'Repository',
            'Request',
            'Route',
            'SubAction',
            'Task',
            'Transformer'
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
                return $app['Apiato\Core\Generator\Commands\\' . $class . 'Generator'];
            });

            $this->commands("command.porto.$lowerClass");
        }
    }
}
