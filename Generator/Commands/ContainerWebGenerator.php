<?php

namespace Apiato\Core\Generator\Commands;

use Apiato\Core\Generator\GeneratorCommand;
use Apiato\Core\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ContainerWebGenerator extends GeneratorCommand implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['url', null, InputOption::VALUE_OPTIONAL, 'The base URI of all endpoints (/stores, /cars, ...)']
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:generate:container:web';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Container for apiato from scratch (WEB Part)';
    /**
     * The type of class being generated.
     */
    protected string $fileType = 'Container';
    /**
     * The structure of the file path.
     */
    protected string $pathStructure = '{section-name}/{container-name}/*';
    /**
     * The structure of the file name.
     */
    protected string $nameStructure = '{file-name}';
    /**
     * The name of the stub file.
     */
    protected string $stubName = 'composer.stub';

    /**
     * @return array
     */
    public function getUserInputs()
    {
        $ui = 'web';

        // container name as inputted and lower
        $sectionName = $this->sectionName;
        $_sectionName = Str::lower($this->sectionName);

        // container name as inputted and lower
        $containerName = $this->containerName;
        $_containerName = Str::lower($this->containerName);

        // name of the model (singular and plural)
        $model = $this->containerName;
        $models = Pluralizer::plural($model);

        // add the README file
        $this->printInfoMessage('Generating README File');
        $this->call('apiato:generate:readme', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'README',
        ]);

        // create the configuration file
        $this->printInfoMessage('Generating Configuration File');
        $this->call('apiato:generate:configuration', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => $_containerName,
        ]);

        // create the MainServiceProvider for the container
        $this->printInfoMessage('Generating MainServiceProvider');
        $this->call('apiato:generate:serviceprovider', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'MainServiceProvider',
            '--stub' => 'mainserviceprovider',
        ]);

        // create the model and repository for this container
        $this->printInfoMessage('Generating Model and Repository');
        $this->call('apiato:generate:model', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => $model,
            '--repository' => true,
        ]);

        // create the migration file for the model
        $this->printInfoMessage('Generating a basic Migration file');
        $this->call('apiato:generate:migration', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'create_' . $models . '_table',
            '--tablename' => $models,
        ]);

        // create the default routes for this container
        $this->printInfoMessage('Generating Default Routes');
        $version = 1;
        $doctype = 'private';

        // get the URI and remove the first trailing slash
        $url = Str::lower($this->checkParameterOrAsk('url', 'Enter the base URI for *all* WEB endpoints (foo/bar)', Str::lower($models)));
        $url = ltrim($url, '/');

        $this->printInfoMessage('Creating Requests for Routes');
        $this->printInfoMessage('Generating Default Actions');
        $this->printInfoMessage('Generating Default Tasks');

        $routes = [
            [
                'stub' => 'GetAll',
                'name' => 'GetAll' . $models,
                'operation' => 'index',
                'verb' => 'GET',
                'url' => $url,
                'action' => 'GetAll' . $models . 'Action',
                'request' => 'GetAll' . $models . 'Request',
                'task' => 'GetAll' . $models . 'Task',
            ],
            [
                'stub' => 'Find',
                'name' => 'Find' . $model . 'ById',
                'operation' => 'show',
                'verb' => 'GET',
                'url' => $url . '/{id}',
                'action' => 'Find' . $model . 'ById' . 'Action',
                'request' => 'Find' . $model . 'ById' . 'Request',
                'task' => 'Find' . $model . 'ById' . 'Task',
            ],
            [
                'stub' => null,
                'name' => 'Create' . $model,
                'operation' => 'create',
                'verb' => 'GET',
                'url' => $url . '/create',
                'action' => null,
                'request' => 'Create' . $model . 'Request',
                'task' => null,
            ],
            [
                'stub' => 'Create',
                'name' => 'Store' . $model,
                'operation' => 'store',
                'verb' => 'POST',
                'url' => $url . '/store',
                'action' => 'Create' . $model . 'Action',
                'request' => 'Store' . $model . 'Request',
                'task' => 'Create' . $model . 'Task',
            ],
            [
                'stub' => null,
                'name' => 'Edit' . $model,
                'operation' => 'edit',
                'verb' => 'GET',
                'url' => $url . '/{id}/edit',
                'action' => null,
                'request' => 'Edit' . $model . 'Request',
                'task' => null,
            ],
            [
                'stub' => 'Update',
                'name' => 'Update' . $model,
                'operation' => 'update',
                'verb' => 'PATCH',
                'url' => $url . '/{id}',
                'action' => 'Update' . $model . 'Action',
                'request' => 'Update' . $model . 'Request',
                'task' => 'Update' . $model . 'Task',
            ],
            [
                'stub' => 'Delete',
                'name' => 'Delete' . $model,
                'operation' => 'delete',
                'verb' => 'DELETE',
                'url' => $url . '/{id}',
                'action' => 'Delete' . $model . 'Action',
                'request' => 'Delete' . $model . 'Request',
                'task' => 'Delete' . $model . 'Task',
            ],
        ];

        foreach ($routes as $route) {
            $this->call('apiato:generate:route', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['name'],
                '--ui' => $ui,
                '--operation' => $route['operation'],
                '--doctype' => $doctype,
                '--docversion' => $version,
                '--url' => $route['url'],
                '--verb' => $route['verb'],
            ]);

            $this->call('apiato:generate:request', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['request'],
                '--ui' => $ui,
            ]);

            if ($route['action'] != null || $route['stub'] != null) {
                $this->call('apiato:generate:action', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['action'],
                    '--model' => $model,
                    '--stub' => $route['stub'],
                ]);
            }

            if ($route['task'] != null || $route['stub'] != null) {
                $this->call('apiato:generate:task', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['task'],
                    '--model' => $model,
                    '--stub' => $route['stub'],
                ]);
            }
        }

        // finally generate the controller
        $this->printInfoMessage('Generating Controller to wire everything together');
        $this->call('apiato:generate:controller', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'Controller',
            '--ui' => $ui,
            '--stub' => 'crud.' . $ui,
        ]);

        $this->printInfoMessage('Generating Composer File');
        return [
            'path-parameters' => [
                'section-name' => $this->sectionName,
                'container-name' => $this->containerName,
            ],
            'stub-parameters' => [
                '_section-name' => $_sectionName,
                'section-name' => $this->sectionName,
                '_container-name' => $_containerName,
                'container-name' => $containerName,
                'class-name' => $this->fileName,
            ],
            'file-parameters' => [
                'file-name' => $this->fileName,
            ],
        ];
    }

    /**
     * Get the default file name for this component to be generated
     */
    public function getDefaultFileName(): string
    {
        return 'composer';
    }

    public function getDefaultFileExtension(): string
    {
        return 'json';
    }
}
