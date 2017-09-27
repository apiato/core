<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ContainerComposerGenerator
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 */
class ContainerGenerator extends GeneratorCommand implements ComponentsGenerator
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:container';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Container for apiato from scratch';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $fileType = 'Container';

    /**
     * The structure of the file path.
     *
     * @var  string
     */
    protected $pathStructure = '{container-name}/*';

    /**
     * The structure of the file name.
     *
     * @var  string
     */
    protected $nameStructure = '{file-name}';

    /**
     * The name of the stub file.
     *
     * @var  string
     */
    protected $stubName = 'container/composer.stub';

    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     *
     * @var  array
     */
    public $inputs = [
        ['ui', null, InputOption::VALUE_OPTIONAL, 'The user-interface to generate the Controller for.'],
        ['doctype', null, InputOption::VALUE_OPTIONAL, 'The type of all endpoints to be generated (private, public)'],
        ['docversion', null, InputOption::VALUE_OPTIONAL, 'The version of all endpoints to be generated (1, 2, ...)'],
        ['url', null, InputOption::VALUE_OPTIONAL, 'The base URI of all endpoints (/stores, /cars, ...)'],
    ];

    /**
     * urn mixed|void
     */
    public function getUserInputs()
    {
        $ui = Str::lower($this->checkParameterOrChoice('ui', 'Select the UI for this container', ['API', 'WEB']));

        // containername as inputted and lower
        $containerName = $this->containerName;
        $_containerName = Str::lower($this->containerName);

        // name of the model (singular and plural)
        $model = $this->containerName;
        $models = Pluralizer::plural($model);

        // create the configuration file
        $this->printInfoMessage('Generating Configuration File');
        Artisan::call('apiato:container-configuration', [
            '--container'   => $containerName,
            '--file'        => $_containerName,
        ]);

        // create the MainServiceProvider
        $this->printInfoMessage('Generating MainServiceProvider');
        Artisan::call('apiato:container-mainserviceprovider', [
            '--container'   => $containerName,
            '--file'        => 'MainServiceProvider',
        ]);

        // create the model for this container
        $this->printInfoMessage('Generating Model and Repository');
        Artisan::call('apiato:model', [
            '--container'   => $containerName,
            '--file'        => $model,
            '--repository'  => true,
        ]);

        // create the migration file for the model
        $this->printInfoMessage('Generating a basic Migration file');
        Artisan::call('apiato:container-migration', [
            '--container'   => $containerName,
            '--file'        => 'setup_' . Str::lower($_containerName) . '_tables',
            '--tablename'   => $models,
        ]);

        // create a transformer for the model
        $this->printInfoMessage('Generating Transformer for the Model');
        Artisan::call('apiato:transformer', [
            '--container'   => $containerName,
            '--file'        => $containerName . 'Transformer',
            '--model'       => $model,
            '--full'        => 'no',
        ]);

        // create the default routes for this container
        $this->printInfoMessage('Generating Default Routes');
        $version = $this->checkParameterOrAsk('docversion', 'Enter the version for *all* endpoints (integer)', RouteGenerator::DEFAULT_VERSION);
        $doctype = $this->checkParameterOrChoice('doctype', 'Select the type for *all* endpoints', ['private', 'public']);

        // get the URI and remove the first trailing slash
        $url = Str::lower($this->checkParameterOrAsk('url', 'Enter the base URI for all endpoints (foo/bar)'));
        $url = ltrim($url, '/');

        $routes = [
            [
                'stub'      => 'GetAll',
                'name'      => 'GetAll' . $models,
                'operation' => 'getAll' . $models,
                'verb'      => 'GET',
                'url'       => $url,
                'action'    => 'GetAll' . $models . 'Action',
                'request'   => 'GetAll' . $models . 'Request',
            ],
            [
                'stub'      => 'GetOne',
                'name'      => 'Get' . $model . 'ById',
                'operation' => 'get' . $model . 'ById',
                'verb'      => 'GET',
                'url'       => $url . '/{id}',
                'action'    => 'Get' . $model . 'ById' . 'Action',
                'request'   => 'Get' . $model . 'ById' . 'Request',
            ],
            [
                'stub'      => 'Create',
                'name'      => 'Create' . $model,
                'operation' => 'create' . $model,
                'verb'      => 'POST',
                'url'       => $url,
                'action'    => 'Create' . $model . 'Action',
                'request'   => 'Create' . $model . 'Request',
            ],
            [
                'stub'      => 'Update',
                'name'      => 'Update' . $model,
                'operation' => 'update' . $model,
                'verb'      => 'PATCH',
                'url'       => $url . '/{id}',
                'action'    => 'Update' . $model . 'Action',
                'request'   => 'Update' . $model . 'Request',
            ],
            [
                'stub'      => 'Delete',
                'name'      => 'Delete' . $model,
                'operation' => 'delete' . $model,
                'verb'      => 'DELETE',
                'url'       => $url . '/{id}',
                'action'    => 'Delete' . $model . 'Action',
                'request'   => 'Delete' . $model . 'Request',
            ],
        ];
        foreach ($routes as $route)
        {
            Artisan::call('apiato:route', [
                '--container'   => $containerName,
                '--file'        => $route['name'],
                '--ui'          => $ui,
                '--operation'   => $route['operation'],
                '--doctype'     => $doctype,
                '--docversion'  => $version,
                '--url'         => $route['url'],
                '--verb'        => $route['verb'],
            ]);

            Artisan::call('apiato:container-action', [
                '--container'   => $containerName,
                '--file'        => $route['action'],
                '--model'       => $model,
                '--stub'        => $route['stub'],
            ]);

            Artisan::call('apiato:request', [
                '--container'   => $containerName,
                '--file'        => $route['request'],
                '--ui'          => $ui,
            ]);
        }

        // finally generate the controller
        $this->printInfoMessage('Generating Controller to wire everything together');
        Artisan::call('apiato:container-controller', [
            '--container'   => $containerName,
            '--file'        => 'Controller',
            '--ui'          => $ui,
        ]);

        $this->printInfoMessage('Generating Composer File');
        return [
            'path-parameters' => [
                'container-name' => $containerName,
            ],
            'stub-parameters' => [
                '_container-name' => $_containerName,
                'container-name' => $containerName,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated
     *
     * @return string
     */
    public function getDefaultFileName()
    {
        return 'composer';
    }

    public function getDefaultFileExtension()
    {
        return '.json';
    }

}
