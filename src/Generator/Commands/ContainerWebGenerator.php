<?php

namespace Apiato\Generator\Commands;

use Apiato\Generator\Generator;
use Apiato\Generator\Interfaces\ComponentsGenerator;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class ContainerWebGenerator extends Generator implements ComponentsGenerator
{
    /**
     * User required/optional inputs expected to be passed while calling the command.
     * This is a replacement of the `getArguments` function "which reads whenever it's called".
     */
    public array $inputs = [
        ['url', null, InputOption::VALUE_OPTIONAL, 'The base URI of all endpoints (/stores, /cars, ...)'],
        ['controllertype', null, InputOption::VALUE_OPTIONAL, 'The controller type (SAC, MAC)'],
        ['maincalled', false, InputOption::VALUE_NONE],
    ];
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apiato:make:container:web';
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

    public function getUserInputs(): array|null
    {
        $ui = 'web';

        $sectionName = $this->sectionName;
        $_sectionName = Str::lower($this->sectionName);

        $containerName = $this->containerName;
        $_containerName = Str::lower($this->containerName);

        $model = $this->containerName;
        $models = Pluralizer::plural($model);

        $this->printInfoMessage('Generating README File');
        $this->call('apiato:make:readme', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'README',
        ]);

        $this->printInfoMessage('Generating Configuration File');
        $this->call('apiato:make:configuration', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => Str::camel($this->sectionName) . '-' . Str::camel($this->containerName),
        ]);

        $this->printInfoMessage('Generating ServiceProvider');
        $this->call('apiato:make:provider', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => Str::title($this->containerName) . 'ServiceProvider',
            '--stub' => 'service-provider',
        ]);

        $this->printInfoMessage('Generating Model and Repository');
        $this->call('apiato:make:model', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => $model,
            '--repository' => true,
        ]);

        $this->printInfoMessage('Generating a basic Migration file');
        $this->call('apiato:make:migration', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--file' => 'create_' . Str::snake($models) . '_table',
            '--tablename' => Str::snake($models),
        ]);

        $this->printInfoMessage('Generating Default Routes');
        $version = 1;
        $doctype = 'private';

        $url = Str::lower($this->checkParameterOrAsk('url', 'Enter the base URI for all WEB endpoints (foo/bar/{id})', Str::kebab($models)));
        $url = ltrim($url, '/');

        $controllertype = Str::lower($this->checkParameterOrChoice('controllertype', 'Select the controller type (Single or Multi Action Controller)', ['SAC', 'MAC'], 0));

        $this->printInfoMessage('Generating Requests for Routes');
        $this->printInfoMessage('Generating Default Actions');
        $this->printInfoMessage('Generating Default Tasks');
        $this->printInfoMessage('Generating Default Controller/s');

        $routes = [
            [
                'stub' => 'List',
                'name' => 'List' . $models,
                'operation' => 'index',
                'verb' => 'GET',
                'url' => $url,
                'action' => 'List' . $models . 'Action',
                'request' => 'List' . $models . 'Request',
                'task' => 'List' . $models . 'Task',
                'controller' => 'List' . $models . 'Controller',
            ],
            [
                'stub' => 'Find',
                'name' => 'Find' . $model . 'ById',
                'operation' => 'show',
                'verb' => 'GET',
                'url' => $url . '/{id}',
                'action' => 'Find' . $model . 'ByIdAction',
                'request' => 'Find' . $model . 'ByIdRequest',
                'task' => 'Find' . $model . 'ByIdTask',
                'controller' => 'Find' . $model . 'ByIdController',
            ],
            [
                'stub' => 'Store',
                'name' => 'Store' . $model,
                'operation' => 'store',
                'verb' => 'POST',
                'url' => $url . '/store',
                'action' => 'Create' . $model . 'Action',
                'request' => 'Store' . $model . 'Request',
                'task' => 'Create' . $model . 'Task',
                'controller' => 'Store' . $model . 'Controller',
            ],
            [
                'stub' => 'Create',
                'name' => 'Create' . $model,
                'operation' => 'create',
                'verb' => 'GET',
                'url' => $url . '/create',
                'action' => null,
                'request' => 'Create' . $model . 'Request',
                'task' => null,
                'controller' => 'Create' . $model . 'Controller',
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
                'controller' => 'Update' . $model . 'Controller',
            ],
            [
                'stub' => 'Edit',
                'name' => 'Edit' . $model,
                'operation' => 'edit',
                'verb' => 'GET',
                'url' => $url . '/{id}/edit',
                'action' => null,
                'request' => 'Edit' . $model . 'Request',
                'task' => null,
                'controller' => 'Edit' . $model . 'Controller',
            ],
            [
                'stub' => 'Delete',
                'name' => 'Delete' . $model,
                'operation' => 'destroy',
                'verb' => 'DELETE',
                'url' => $url . '/{id}',
                'action' => 'Delete' . $model . 'Action',
                'request' => 'Delete' . $model . 'Request',
                'task' => 'Delete' . $model . 'Task',
                'controller' => 'Delete' . $model . 'Controller',
            ],
        ];

        foreach ($routes as $route) {
            $this->call('apiato:make:request', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['request'],
                '--ui' => $ui,
                '--stub' => $route['stub'],
            ]);

            if (null !== $route['action']) {
                $this->call('apiato:make:action', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['action'],
                    '--ui' => $ui,
                    '--model' => $model,
                    '--stub' => $route['stub'],
                ]);
            }

            if (null !== $route['task']) {
                $this->call('apiato:make:task', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['task'],
                    '--model' => $model,
                    '--stub' => $route['stub'],
                ]);
            }

            $routeArgs = [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => $route['name'],
                '--ui' => $ui,
                '--operation' => $route['operation'],
                '--doctype' => $doctype,
                '--docversion' => $version,
                '--url' => $route['url'],
                '--verb' => $route['verb'],
            ];

            if ('sac' === $controllertype) {
                $this->call('apiato:make:route', [
                    ...$routeArgs,
                    '--controller' => $route['controller'],
                    '--sac' => true,
                ]);

                $this->call('apiato:make:controller', [
                    '--section' => $sectionName,
                    '--container' => $containerName,
                    '--file' => $route['controller'],
                    '--model' => $model,
                    '--ui' => $ui,
                    '--stub' => $route['stub'],
                ]);
            } else {
                $this->call('apiato:make:route', [
                    ...$routeArgs,
                    '--controller' => 'Controller',
                    '--sac' => false,
                ]);
            }
        }

        if ('mac' === $controllertype) {
            $this->printInfoMessage('Generating Controller to wire everything together');
            $this->call('apiato:make:controller', [
                '--section' => $sectionName,
                '--container' => $containerName,
                '--file' => 'Controller',
                '--model' => $model,
                '--ui' => $ui,
                '--stub' => 'crud',
            ]);
        }

        $generateComposerFile = [
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

        if (!$this->option('maincalled')) {
            $this->printInfoMessage('Generating Composer File');

            return $generateComposerFile;
        }

        return null;
    }

    public function getDefaultFileName(): string
    {
        return 'composer';
    }

    public function getDefaultFileExtension(): string
    {
        return 'json';
    }
}
